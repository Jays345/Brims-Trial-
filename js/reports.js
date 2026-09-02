let revenueExpensesChart=null;
let profitTrendChart=null;
let topProductsChart=null;
let lowProductsChart=null;

function showToast(msg,duration=3000){
  const toast = document.getElementById('toast');
  toast.textContent = msg;
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), duration);
}

function loadReports(period='monthly', category='all'){
  fetch(`backend/reports_data.php?period=${period}&category=${category}`)
    .then(r=>r.json())
    .then(data=>{
      // Update cards
      document.getElementById('totalRevenue').textContent = `$${data.totalRevenue.toFixed(2)}`;
      document.getElementById('totalExpenses').textContent = `$${data.totalExpenses.toFixed(2)}`;
      document.getElementById('totalProfit').textContent = `$${data.totalProfit.toFixed(2)}`;
      document.getElementById('mostProfitable').textContent = data.mostProfitableProduct;
      document.getElementById('leastProfitable').textContent = data.leastProfitableProduct;
      document.getElementById('totalOrders').textContent = data.totalOrders;

      // Destroy previous charts
      if(revenueExpensesChart) revenueExpensesChart.destroy();
      if(profitTrendChart) profitTrendChart.destroy();
      if(topProductsChart) topProductsChart.destroy();
      if(lowProductsChart) lowProductsChart.destroy();

      // Revenue vs Expenses Chart
      revenueExpensesChart = new Chart(document.getElementById('revenueExpensesChart').getContext('2d'), {
        type:'line',
        data:{ labels: data.labels, datasets:[
          {label:'Revenue', data:data.revenue, borderColor:'#00ff80', backgroundColor:'rgba(0,255,128,0.3)', fill:true},
          {label:'Expenses', data:data.expenses, borderColor:'#ff5555', backgroundColor:'rgba(255,0,0,0.3)', fill:true}
        ]},
        options:{responsive:true, maintainAspectRatio:false}
      });

      // Profit Trend
      profitTrendChart = new Chart(document.getElementById('profitTrendChart').getContext('2d'), {
        type:'line',
        data:{ labels:data.labels, datasets:[{label:'Profit', data:data.profit, borderColor:'#00bfff', backgroundColor:'rgba(0,191,255,0.3)', fill:true}]},
        options:{responsive:true, maintainAspectRatio:false}
      });

      // Top Products
      topProductsChart = new Chart(document.getElementById('topProductsChart').getContext('2d'), {
        type:'bar',
        data:{ labels:data.topProducts.labels, datasets:[{label:'Profit', data:data.topProducts.data, backgroundColor:'rgba(0,255,128,0.7)'}]},
        options:{responsive:true, maintainAspectRatio:false, scales:{y:{beginAtZero:true}}}
      });

      // Low Products
      lowProductsChart = new Chart(document.getElementById('lowProductsChart').getContext('2d'), {
        type:'bar',
        data:{ labels:data.lowProducts.labels, datasets:[{label:'Profit', data:data.lowProducts.data, backgroundColor:'rgba(255,0,0,0.7)'}]},
        options:{responsive:true, maintainAspectRatio:false, scales:{y:{beginAtZero:true}}}
      });

      // Orders Table
      const tbody = document.getElementById('ordersTable');
      tbody.innerHTML = '';
      data.orders.forEach(o=>{
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${o.id}</td><td>${o.product}</td><td>$${o.profit.toFixed(2)}</td><td>$${o.revenue.toFixed(2)}</td><td>${o.status}</td>`;
        tbody.appendChild(tr);
      });

      showToast('Reports updated');
    })
    .catch(err => console.error(err));
}

// --- Filters ---
document.getElementById('reportPeriod').addEventListener('change', e=>{
  loadReports(e.target.value, document.getElementById('categoryFilter').value);
});
document.getElementById('categoryFilter').addEventListener('change', e=>{
  loadReports(document.getElementById('reportPeriod').value, e.target.value);
});

// --- CSV Download ---
document.getElementById('downloadCSV').addEventListener('click', ()=>{
  let csvContent = "data:text/csv;charset=utf-8,";
  const cards = [
    ['Total Revenue', document.getElementById('totalRevenue').textContent],
    ['Total Expenses', document.getElementById('totalExpenses').textContent],
    ['Total Profit', document.getElementById('totalProfit').textContent],
    ['Most Profitable Product', document.getElementById('mostProfitable').textContent],
    ['Least Profitable Product', document.getElementById('leastProfitable').textContent],
    ['Total Orders', document.getElementById('totalOrders').textContent]
  ];
  cards.forEach(c=>{ csvContent += `${c[0]},${c[1]}\n`; });

  csvContent += "\nOrders Table\n";
  document.querySelectorAll('#ordersTable tr').forEach(row=>{
    const cols = row.querySelectorAll('td');
    csvContent += Array.from(cols).map(td=>td.textContent).join(',') + "\n";
  });

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", `BRIMS_Report_${document.getElementById('reportPeriod').value}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
});

// --- PDF Download ---
document.getElementById('downloadPDF').addEventListener('click', async ()=>{
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF('p','mm','a4');
  let yOffset=10;

  const capture = ['.cards','#revenueExpensesChart','#profitTrendChart','#topProductsChart','#lowProductsChart','table'];
  for(const sel of capture){
    const el = document.querySelector(sel);
    const canvas = await html2canvas(el);
    const imgData = canvas.toDataURL('image/png');
    const imgWidth=190;
    const imgHeight = canvas.height*imgWidth/canvas.width;
    if(yOffset+imgHeight>280){ doc.addPage(); yOffset=10; }
    doc.addImage(imgData,'PNG',10,yOffset,imgWidth,imgHeight);
    yOffset += imgHeight+10;
  }

  doc.save(`BRIMS_Report_${document.getElementById('reportPeriod').value}.pdf`);
});

// --- Initial Load ---
loadReports();
