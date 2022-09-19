
const loc = document.getElementById('location');
const selectedCity = document.getElementById('event_city');

selectedCity.addEventListener('change',(e)=>{
    let idVille = e.target.value;
    if (idVille !== ''){
        loc.removeAttribute('disabled')
        getAssociatedLocation(idVille);
    } else {
        loc.setAttribute('disabled','true')
    }
})

function getAssociatedLocation(idVille) {
    let zip = 0 ;
    fetch('https://127.0.0.1:8000/api/location/'+idVille).then((res)=>
        res.json()
    ).then((data)=>{
        console.log(data)
        zip = data[0];
        let option ="<option value='0'>Choisir un lieux</option>";
        data[1].map(loc=>{
            option += `<option value="${ loc.id }">${ loc.name }</option>`
        })
        loc.innerHTML = option
    })
    loc.addEventListener('change',(e)=>{
        const infos = document.getElementById('info');
        let idLocation = e.target.value;

        fetch('https://127.0.0.1:8000/api/detail/'+idLocation).then((res)=>
            res.json()
        ).then((data)=>{
            console.log(data);
            infos.innerHTML =
                `<div><p class="text-primary"> Rue :${data.street}</p> </div>
                     <div>
                        <p class="text-primary">code postal :${zip}</p>
                    </div>
                    <div>
                        <div> <label class="form-label">Latitude<input type="number" class="form-control" readonly value="${data.latitude}"></label></div>
                        <div> <label class="form-label">Longitude<input type="number" class="form-control" readonly value="${data.longitude}"></label></div>
                    </div>`;
        })
    })
}