document.addEventListener('DOMContentLoaded', ()=>{
    const timeIn = document.querySelector('[name=time_in]');
    const timeOut = document.querySelector('[name=time_out]');
    const hoursSpan = document.createElement('div');
    hoursSpan.id = 'calcHours';
    timeOut.parentNode.appendChild(hoursSpan);
    function calc(){
        if(timeIn.value && timeOut.value){
            const s = new Date('1970-01-01T'+timeIn.value+':00');
            const e = new Date('1970-01-01T'+timeOut.value+':00');
            let diff = (e - s) / 3600000;
            if(diff<0) diff = 0;
            hoursSpan.textContent = 'Hours: ' + diff.toFixed(2);
        }
    }
    timeIn.addEventListener('change', calc);
    timeOut.addEventListener('change', calc);
});
