<?php
function askOllamaForSQL($userMessage, $schema) {
    $payload = [
        "model" => "llama3.1",
        "prompt" => "You are an AI SQL generator for a restaurant system (BRIMS).
User question: \"$userMessage\"

DATABASE SCHEMA:
$schema

Rules:
- ALWAYS return ONLY SQL.
- Do NOT explain.
- Do NOT add code fences.
- Use correct column names.
- Use MySQL syntax.
- If dates are needed, assume current_date.
- If data spans tables, JOIN them.
- If question is ambiguous, pick the most logical interpretation.
- LIMIT large results to 10 rows.

Return only SQL and nothing else.
"
    ];

    $ch = curl_init("http://localhost:11434/api/generate");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);

    if (!$response) return false;
    $json = json_decode($response, true);
    return $json["response"] ?? false;
}
?>
