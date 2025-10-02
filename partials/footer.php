<?php // partials/footer.php ?>
</main> <!-- Cierre del container principal -->

<footer class="bg-gray-900 text-gray-400 text-center p-4 mt-auto">
    <div class="container mx-auto">
        &copy; <?= date('Y') ?> - CRM de Ventas. Todos los derechos reservados.
    </div>
</footer>

<!-- Modal de Rechazo con Tailwind (Dark Mode) -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center hidden z-50">
    <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center border-b border-gray-700 pb-3">
            <h3 class="text-xl font-semibold text-gray-200">Motivo del Rechazo</h3>
            <button id="closeModalBtn" class="text-gray-400 hover:text-white text-2xl">&times;</button>
        </div>
        <div class="mt-4">
            <p class="text-sm text-gray-400 mb-2">Por favor, especifica claramente por qué se rechaza este formulario.</p>
            <form id="rejectForm">
                <input type="hidden" id="formIdToReject" name="form_id">
                <div>
                    <label for="rejectionReason" class="sr-only">Motivo:</label>
                    <textarea class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="rejectionReason" name="reason" rows="4" required></textarea>
                </div>
            </form>
        </div>
        <div class="mt-6 flex justify-end space-x-4">
            <button id="cancelModalBtn" class="px-4 py-2 bg-gray-600 text-gray-200 rounded-md hover:bg-gray-500">Cancelar</button>
            <button id="confirmRejectBtn" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Confirmar Rechazo</button>
        </div>
    </div>
</div>

<!-- Lógica JavaScript Unificada -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- LÓGICA PARA MENÚ MÓVIL ---
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // --- FUNCIÓN GENÉRICA PARA MANEJAR RESPUESTAS DE LA API ---
    function handleApiResponse(response) {
        response.json().then(data => {
            if (data.success) {
                alert('Acción realizada con éxito.');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Ocurrió un problema.'));
            }
        });
    }

    // --- ACCIÓN: CAMBIAR ROL DE USUARIO ---
    document.querySelectorAll('.user-role-selector').forEach(selector => {
        selector.addEventListener('change', function() {
            const payload = { action: 'change_role', user_id: this.dataset.userId, new_role: this.value };
            if (confirm(`¿Confirmas el cambio de rol a ${this.value}?`)) {
                fetch('api.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) }).then(handleApiResponse);
            } else { location.reload(); }
        });
    });

    // --- ACCIÓN: APROBAR FORMULARIO ---
    document.querySelectorAll('.form-action-btn[data-action="aprobado"]').forEach(button => {
        button.addEventListener('click', function() {
            const payload = { action: 'update_status', form_id: this.dataset.formId, status: 'aprobado', reason: '' };
            if (confirm('¿Estás seguro de que quieres APROBAR este formulario?')) {
                fetch('api.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) }).then(handleApiResponse);
            }
        });
    });

    // --- ACCIÓN: RECHAZAR FORMULARIO (MANEJO DEL MODAL DE TAILWIND) ---
    const rejectModal = document.getElementById('rejectModal');
    if (rejectModal) {
        const formIdInput = document.getElementById('formIdToReject');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cancelModalBtn = document.getElementById('cancelModalBtn');
        const confirmRejectBtn = document.getElementById('confirmRejectBtn');

        const closeModal = () => rejectModal.classList.add('hidden');

        document.querySelectorAll('.reject-modal-btn').forEach(button => {
            button.addEventListener('click', () => {
                if(formIdInput) formIdInput.value = button.dataset.formId;
                rejectModal.classList.remove('hidden');
            });
        });

        if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
        if (cancelModalBtn) cancelModalBtn.addEventListener('click', closeModal);

        if (confirmRejectBtn) {
            confirmRejectBtn.addEventListener('click', () => {
                const reason = document.getElementById('rejectionReason').value.trim();
                if (reason === '') {
                    alert('Debes especificar un motivo para el rechazo.');
                    return;
                }
                const payload = { action: 'update_status', form_id: formIdInput.value, status: 'rechazado', reason: reason };
                fetch('api.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) }).then(handleApiResponse);
                closeModal();
            });
        }
    }

    // --- ACCIÓN: MARCAR COMO ENTREGADO ---
    document.querySelectorAll('.mark-delivered-btn').forEach(button => {
        button.addEventListener('click', function() {
            const payload = { action: 'mark_as_delivered', form_id: this.dataset.formId };
            if (confirm(`¿Confirmas que la venta #${this.dataset.formId} ha sido ENTREGADA?`)) {
                fetch('api.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) }).then(handleApiResponse);
            }
        });
    });

    // --- ACCIÓN: CANCELAR APROBACIÓN ---
    document.querySelectorAll('.cancel-approval-btn').forEach(button => {
        button.addEventListener('click', function() {
            const formId = this.dataset.formId;
            const payload = { action: 'cancel_approval', form_id: formId };

            if (confirm(`¿Estás seguro de que quieres cancelar la aprobación de la venta #${formId}?\n\nEsta acción la devolverá al estado "En Revisión".`)) {
                fetch('api.php', { 
                    method: 'POST', 
                    headers: {'Content-Type': 'application/json'}, 
                    body: JSON.stringify(payload) 
                }).then(handleApiResponse);
            }
        });
    });

    // --- LÓGICA DE IMPRESIÓN ---
    function printTableData(title, rowSelector, headers, dataKeys, sortByVendor = false) {
        let rowsToPrint = Array.from(document.querySelectorAll(rowSelector));
        if (rowsToPrint.length === 0) {
            alert(`No hay filas para imprimir con el estado seleccionado.`);
            return;
        }

        if (sortByVendor) {
            rowsToPrint.sort((a, b) => (a.dataset.vendedor || '').localeCompare(b.dataset.vendedor || ''));
        }

        let tableHeaders = headers.map(h => `<th>${h}</th>`).join('');
        let tableRows = '';
        let counter = 1;
        rowsToPrint.forEach(row => {
            tableRows += '<tr>';
            dataKeys.forEach(key => {
                let value = row.dataset[key] || '';
                if (key === 'enum') { value = counter++; }
                else if (key === 'estadoFinal') {
                    const estadoVenta = row.dataset.estado;
                    const estadoEntrega = row.dataset.estadoEntrega;
                    if (estadoVenta === 'rechazado') value = 'Rechazado';
                    else if (estadoVenta === 'aprobado' && estadoEntrega === 'entregado') value = 'Entregado';
                    else if (estadoVenta === 'aprobado') value = 'Aprobado';
                    else value = 'En Revisión';
                }
                tableRows += `<td>${value}</td>`;
            });
            tableRows += '</tr>';
        });

        let printContent = `<html><head><title>${title}</title><style>body{font-family:Arial,sans-serif;margin:20px}table{width:100%;border-collapse:collapse;margin-top:20px;font-size:12px}th,td{border:1px solid #ccc;padding:8px;text-align:left}th{background-color:#f2f2f2;font-weight:bold}h1{text-align:center;border-bottom:2px solid #333;padding-bottom:10px}@media print{body{margin:0}}</style></head><body><h1>${title}</h1><p>Fecha: ${new Date().toLocaleDateString('es-AR',{hour:'2-digit',minute:'2-digit'})}</p><table><thead><tr>${tableHeaders}</tr></thead><tbody>${tableRows}</tbody></table></body></html>`;
        
        const printWindow = window.open('', '_blank');
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => { printWindow.print(); printWindow.close(); }, 250);
    }

    // Botón para imprimir Entregas Pendientes
    const printPendingBtn = document.getElementById('printPendingBtn');
    if (printPendingBtn) {
        const headers = ['#Venta', 'Vendedor', 'Cliente', 'Artículo', 'Domicilio', 'Contacto', 'Financiación'];
        const keys = ['id', 'vendedor', 'cliente', 'articulo', 'domicilio', 'contacto', 'financiacion'];
        printPendingBtn.addEventListener('click', () => printTableData('Listado de Entregas Pendientes', 'tr.pendiente-row', headers, keys, true));
    }

    // Botón para imprimir Entregas Realizadas
    const printDeliveredBtn = document.getElementById('printDeliveredBtn');
    if (printDeliveredBtn) {
        const headers = ['#Venta', 'Vendedor', 'Cliente', 'Artículo', 'Domicilio', 'Contacto', 'Financiación'];
        const keys = ['id', 'vendedor', 'cliente', 'articulo', 'domicilio', 'contacto', 'financiacion'];
        printDeliveredBtn.addEventListener('click', () => printTableData('Listado de Entregas Realizadas', 'tr.entregado-row', headers, keys, true));
    }

    // Botón para imprimir Formularios en Revisión
    const printInReviewBtn = document.getElementById('printInReviewBtn');
    if (printInReviewBtn) {
        const headers = ['#Venta', 'Vendedor', 'Cliente', 'Artículo', 'Domicilio', 'Contacto', 'Fecha'];
        const keys = ['id', 'vendedor', 'cliente', 'articulo', 'domicilio', 'contacto', 'fecha'];
        printInReviewBtn.addEventListener('click', () => printTableData('Formularios en Revisión', 'tr.en-revision-row', headers, keys, false));
    }
    
    // ===================================================================
    // == INICIO DEL CAMBIO: Actualizar lógica para "Imprimir Reporte"    ==
    // ===================================================================
    const printSalesReportBtn = document.getElementById('printSalesReportBtn');
    if (printSalesReportBtn) {
        const headers = ['#', '# Venta', 'Vendedor', 'Cliente', 'Artículo', 'Fecha Carga', 'Fecha Estado', 'Estado Final'];
        const keys = ['enum', 'id', 'vendedor', 'cliente', 'articulo', 'fechaCarga', 'fechaEstado', 'estadoFinal'];
        printSalesReportBtn.addEventListener('click', () => printTableData('Reporte General de Ventas', 'tr.report-row', headers, keys, false));
    }
    // ===================================================================
    // == FIN DEL CAMBIO ==
    // ===================================================================

    // --- REGISTRO DEL SERVICE WORKER (PWA) ---
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('Service Worker registrado con éxito:', registration);
                })
                .catch(error => {
                    console.log('Fallo en el registro del Service Worker:', error);
                });
        });
    }
});
</script>
</body>
</html>
