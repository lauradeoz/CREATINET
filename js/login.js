//crear referencias a las modales
const modalRegistro = document.getElementById('modalRegistro');
const modalRecuperar = document.getElementById('modalRecuperar');

//referencias a los encales que abren las modales
const btnRecuperar = document.querySelector('.abrir-modal-recuperar');
const btnRegistro = document.querySelector('.abrir-modal-registro');

//referencias al span que cierra la modal
const spanRegistro = document.querySelector('.cerrarRegistro');
const spanRecuperar = document.querySelector('.cerrarRecuperar');

//abrir la modal del registro
btnRegistro.addEventListener('click',() => {
    modalRegistro.classList.add('show');
})

//cerrar la modal registro
spanRegistro.onclick = function(){
    modalRegistro.classList.remove('show');
}

btnRecuperar.addEventListener('click', () =>{
    modalRecuperar.classList.add('show');
})

spanRecuperar.addEventListener('click', () => {
    modalRecuperar.classList.remove('show');
})

//cerrar modal cuando el usuario hace click fuera de la modal
window.onclick = function(event){
    if(event.target === modalRegistro){
        modalRegistro.classList.remove('show');
    }
    if(event.target === modalRecuperar){
        modalRecuperar.classList.remove('show');
    }
}