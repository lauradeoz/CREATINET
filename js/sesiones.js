// js/sesiones.js
// Este script maneja la funcionalidad de cerrar sesión del usuario.

// Añade un 'event listener' al elemento con el ID 'cerrarSesion'.
// Se activa cuando se hace clic en este elemento (que debería ser el botón/enlace de cerrar sesión).
document.getElementById('cerrarSesion').addEventListener('click', () => {
    // Muestra un cuadro de diálogo de confirmación al usuario.
    const confirmado = confirm("¿Seguro que quieres cerrar sesión?");

    // Si el usuario confirma que quiere cerrar sesión.
    if (confirmado) {
        // Realiza una solicitud fetch (POST) al script 'logout.php'.
        // Esta solicitud se encarga de destruir la sesión en el servidor.
        fetch('logout.php', {
            method: 'POST' // Se utiliza el método POST para la acción de cerrar sesión.
        })
        .then(() => {
            // Una vez que la solicitud a 'logout.php' se ha completado (sin importar la respuesta del servidor),
            // redirige al usuario a la página de inicio de sesión ('login.php').
            window.location.href = 'login.php';
        })
        .catch(error => {
            // Captura y registra cualquier error que ocurra durante la solicitud fetch.
            console.error('Error al cerrar sesión:', error);
            // Opcionalmente, podrías mostrar un mensaje de error al usuario aquí.
            alert('Hubo un error al intentar cerrar sesión. Por favor, inténtalo de nuevo.');
        });
    }
});