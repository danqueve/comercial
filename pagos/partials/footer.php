</div> <!-- Cierre del <div class="container mx-auto print-container"> abierto en header.php -->

<!-- SCRIPTS DE JAVASCRIPT -->
<script>
// Este script se ejecuta una vez que todo el contenido HTML de la página ha sido cargado.
document.addEventListener('DOMContentLoaded', function() {

    // El código de cálculo de totales solo debe ejecutarse en la página de 'Rutas'.
    // Verificamos si existe el elemento 'total-estimado' para asegurarnos de que estamos en la página correcta.
    if (document.getElementById('total-estimado')) {
        
        // Seleccionamos todos los elementos del DOM que necesitamos.
        const inputsCobrado = document.querySelectorAll('.monto-cobrado-input');
        const totalEstimadoEl = document.getElementById('total-estimado');
        const totalCobradoEl = document.getElementById('total-cobrado');
        const totalFaltanteEl = document.getElementById('total-faltante');
        
        // Obtenemos el valor de la cobranza estimada que pasamos desde PHP a través de un atributo data-valor.
        // Usamos parseFloat para convertirlo a un número. Si no existe, el valor es 0.
        const cobranzaEstimada = parseFloat(totalEstimadoEl.dataset.valor) || 0;

        /**
         * Formatea un número como moneda local (ARS) usando la API de internacionalización del navegador.
         * @param {number} value El número a formatear.
         * @returns {string} La cadena de texto formateada.
         */
        function formatCurrencyJS(value) {
            return new Intl.NumberFormat('es-AR', { 
                style: 'currency', 
                currency: 'ARS', 
                minimumFractionDigits: 0, 
                maximumFractionDigits: 0 
            }).format(value);
        }

        /**
         * Calcula los totales y actualiza las tarjetas de resumen en la interfaz.
         */
        function actualizarTotales() {
            let totalCobrado = 0;
            
            // Recorremos cada uno de los inputs de "Monto Cobrado".
            inputsCobrado.forEach(input => {
                const valor = parseFloat(input.value);
                // Si el valor es un número válido, lo sumamos al total.
                if (!isNaN(valor)) { 
                    totalCobrado += valor; 
                }
            });

            // Calculamos el monto faltante.
            const faltante = cobranzaEstimada - totalCobrado;

            // Actualizamos el contenido de las tarjetas de resumen con los valores formateados.
            totalEstimadoEl.textContent = formatCurrencyJS(cobranzaEstimada);
            totalCobradoEl.textContent = formatCurrencyJS(totalCobrado);
            totalFaltanteEl.textContent = formatCurrencyJS(faltante);
        }

        // Agregamos un "escuchador de eventos" a cada input.
        // Cada vez que el usuario escribe algo en un campo de "Monto Cobrado", se llama a la función actualizarTotales.
        inputsCobrado.forEach(input => input.addEventListener('input', actualizarTotales));

        // Llamamos a la función una vez al cargar la página para establecer los valores iniciales.
        actualizarTotales();
    }
});
</script>

</body>
</html>
