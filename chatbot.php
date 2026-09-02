<?php
// chatbot.php - AI SQL agent + execution 
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// DB connection 
$host = '127.0.0.1';
$user = 'root';
$db   = 'smart_biz'; 

$conn = new mysqli($host, $user, $db);
if ($conn->connect_error) {
    echo json_encode(['reply' => 'DB connection failed: ' . $conn->connect_error]);
    exit;
}

$message = trim($_POST['message'] ?? '');
if ($message === '') {
    echo json_encode(['reply' => 'Empty message.']);
    exit;
}

// 1) Build schema description dynamically
$schema = "";
$tablesRes = $conn->query("SHOW TABLES");
if ($tablesRes) {
    while ($t = $tablesRes->fetch_row()) {
        $table = $t[0];
        $schema .= "TABLE $table:\n";
        $colsRes = $conn->query("SHOW COLUMNS FROM `$table`");
        if ($colsRes) {
            while ($c = $colsRes->fetch_assoc()) {
                $schema .= "  - {$c['Field']} ({$c['Type']})\n";
            }
        }
    }
}

$prompt = <<<PROMPT
You are an SQL generator for a MySQL inventory system called BRIMS.

Your ONLY job is:
 Convert the user's message into a SINGLE valid MySQL **SELECT** query.

DATABASE SCHEMA:
{$schema}

STRICT RULES:
- Output **ONLY** a valid MySQL SELECT query.
- NO explanations.
- NO natural language.
- NO comments.
- NO markdown.
- NO code blocks.
- MUST use correct table + column names from schema.
- If the user requests "today" or "current day", assume:
  DATE(`created_at`) = CURDATE()
- If the user requests sales, use the **sales** table.
- If ambiguous, choose the most relevant table and add: LIMIT 10.
- NEVER produce INSERT, UPDATE, DELETE, DROP, or ALTER.

User request: "{$message}"
PROMPT;


$ollamaPayload = [
    "model" => "llama3.1:latest",
    "prompt" => $prompt,
    "stream" => false
];

$ch = curl_init("http://localhost:11434/api/generate");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($ollamaPayload));
$ollamaResp = curl_exec($ch);
$curlErr = curl_error($ch);
curl_close($ch);

if ($ollamaResp === false || $curlErr) {
    echo json_encode(['reply' => 'Error contacting Ollama: ' . $curlErr]);
    exit;
}

$ollamaJson = json_decode($ollamaResp, true);
$sql = trim($ollamaJson['response'] ?? '');

//  Basic safety checks
$sql_lc = ltrim(strtolower($sql));
if ($sql === '') {
    echo json_encode(['reply' => "AI returned no SQL. Raw response: " . substr($ollamaResp, 0, 1000)]);
    exit;
}
if (strpos($sql_lc, 'select') !== 0) {
    echo json_encode(['reply' => "AI did not return a SELECT statement. SQL returned: {$sql}"]);
    exit;
}

// Force a LIMIT if not present to avoid huge results
if (!preg_match('/\blimit\b/i', $sql)) {
    // append limit 100
    $sql = rtrim($sql, "; \t\n\r") . " LIMIT 100";
}

//  Execute query
$result = $conn->query($sql);
if ($result === false) {
    echo json_encode(['reply' => "SQL error: " . $conn->error . "\nGenerated SQL: {$sql}"]);
    exit;
}

//  Fetch results
$rows = [];
while ($r = $result->fetch_assoc()) {
    $rows[] = $r;
}

// Format reply
if (count($rows) === 0) {
    $reply = "No results.\nSQL: {$sql}";
} else {
    // Build a concise table-like string (first 10 rows)
    $replyRows = json_encode($rows, JSON_PRETTY_PRINT);
    $reply = "Results (" . count($rows) . " rows):\n" . $replyRows . "\n\nSQL: {$sql}";
}

// Close DB
$conn->close();

// 7) Return JSON
echo json_encode(['reply' => $reply]);
exit;
