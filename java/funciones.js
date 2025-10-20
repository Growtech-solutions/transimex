function obtenerNombreProyecto() {
    var ot = document.getElementById("ot").value;
    if (ot.trim() !== "") {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("nombreDelProyecto").value = this.responseText;
            }
        };
        xhttp.open("GET", "../php/obtener_nombre_proyecto.php?ot=" + ot, true);
        xhttp.send();
    } else {
        document.getElementById("nombreDelProyecto").value = "";
    }
}
document.addEventListener("DOMContentLoaded", function() {
    var celdasFecha = document.querySelectorAll("table .fecha");
    celdasFecha.forEach(function(celda) {
        var fecha = new Date(celda.innerText);
        var diferenciaMeses = calcularDiferenciaMeses(fecha);
        if (diferenciaMeses <= 3) {
            celda.classList.add("rojo");
        } else if (diferenciaMeses > 3 && diferenciaMeses <= 6) {
            celda.classList.add("amarillo");
        } else {
            celda.classList.add("verde");
        }
    });
});

    
function calcularDiferenciaMeses(fecha) {
    var fechaActual = new Date();
    var diferencia = fechaActual.getMonth() - fecha.getMonth() + (12 * (fechaActual.getFullYear() - fecha.getFullYear()));
    return diferencia;
}
        
