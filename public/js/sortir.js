function afficherLieux(ville){
    if(ville != "")
    {
        viderLesChampsDeLieu();

        // Actualiser la liste des lieux et le code postal
        let url = 'http://localhost/sortir/public/api/lieu/' + ville;
        fetch(url,{method: "GET", headers: {'Accept': 'application/json'}})
            .then(response => response.json())
            .then(response => {
                let options = '<option value="" disabled selected>Veuillez selectionner un lieu</option>';
                response.lieux.map(lieu => {
                    options += `<option value="${lieu.nom}">${lieu.nom}</option>`;
                })
                document.querySelector('#lieux').innerHTML = options;
                document.querySelector('#cp').setAttribute('value',response.CP);
            })
    }
    else
    {
        document.querySelector('#lieux').innerHTML ="";
        document.querySelector('#cp').setAttribute('value',"");
        viderLesChampsDeLieu();

    }

}

function afficherDetailLieu(nomLieu){
    if(nomLieu != "")
    {
        let url = 'http://localhost/sortir/public/api/lieu/detail/' + nomLieu;
        fetch(url,{method: "GET", headers: {'Accept': 'application/json'}})
            .then(response => response.json())
            .then(response => {
                document.querySelector('#rue').setAttribute('value',response.rue);
                document.querySelector('#latitude').setAttribute('value',response.latitude);
                document.querySelector('#longitude').setAttribute('value',response.longitude);

            })
    }
    else
    {
        viderLesChampsDeLieu();
    }
}

function viderLesChampsDeLieu(){
    document.querySelector('#rue').setAttribute('value',"");
    document.querySelector('#latitude').setAttribute('value',"");
    document.querySelector('#longitude').setAttribute('value',"");
}

const selectVille = document.querySelector("#villes");
const selectLieu = document.querySelector("#lieux");
const btnEnregistrer = document.querySelector('#enregistrer');
const btnPublier = document.querySelector('#publier');
const btnAnnuler = document.querySelector('#confirmer');

selectVille.addEventListener("change", () => {
    let nomVille = document.querySelector('#villes').value;
    afficherLieux(nomVille);
});

selectLieu.addEventListener("change", () => {
    let nomLieu = document.querySelector('#lieux').value;
    afficherDetailLieu(nomLieu);
});

btnEnregistrer.addEventListener("click", () => {
    let rpOK = confirm("Voulez vous enregistrer la sortie maintenant");
    if(rpOK != true)
    {
        btnEnregistrer.value = null;
    }
})

btnPublier.addEventListener("click", () => {
    let rpOK = confirm("Voulez vous publier la sortie maintenant");
    if(rpOK != true)
    {
        btnPublier.value = null;
    }
})

btnAnnuler.addEventListener("click", () => {
    let rpOK = confirm("Voulez vous annuler la sortie maintenant");
    if(rpOK != true)
    {
        btnAnnuler.value = null;
    }
})
