function showToast(msg, type='info'){
    const div = document.createElement('div');
    div.textContent = msg;
    div.className = 'fixed bottom-4 right-4 p-4 rounded shadow-lg ' +
        (type==='error'?'bg-red-500':'bg-green-500');
    document.body.appendChild(div);
    setTimeout(()=>{ div.remove(); }, 3000);
}
