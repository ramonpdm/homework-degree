<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Internamientos - Ramón Perdomo</title>
        <link rel="stylesheet" href="assets/style.css">
    </head>
    <body>
        <div class="card">
            <h4>Internamientos</h4>
            <h5>Nombre: <span id="nombre"></span></h5>
            <form id="add_internamiento" style="text-align: center">
                <div style="display: flex; justify-content: center">
                    <input type="date" style="margin-right: 5px" name="fecha" id="fecha" required>
                    <input type="text" style="margin-right: 5px" name="centro_medico" id="centro_medico" placeholder="Centro Médico" required>
                    <input type="text" style="margin-right: 5px" name="diagnostico" id="diagnostico" placeholder="Diagnóstico" required>
                </div>
                <button class="add-btn" type="submit">Añadir Nuevo Internamiento</button>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Centro Médico</th>
                        <th>Diagnóstico</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="internamientos-list">
                    <tr>
                        <td colspan="4">Cargando...</td>
                    </tr>
                </tbody>
            </table>
            <br>
            <button type="button" onclick="history.back()">Atrás</button>
            <a href="index.php"><button type="button">Inicio</button></a>
        </div>

        <script>
            let cache = [];
            let id = null;
            let persona = {};

            document.addEventListener('DOMContentLoaded', async function () {
                const params = new URLSearchParams(window.location.search);
                id = parseInt(params.get('id'));
                await loadPersonas();

                if (!persona) {
                    alert('Persona no encontrada');
                    window.location.href = 'index.php';
                    return;
                }

                const nombre = document.getElementById('nombre');
                nombre.textContent = persona.nombre;

                if (id) {
                    await fetchInternamientos(id);
                } else {
                    window.location.href = 'index.php';
                }

                const addInternamientoForm = document.getElementById('add_internamiento');
                addInternamientoForm.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const fechaInput = document.getElementById('fecha');
                    const centroMedicoInput = document.getElementById('centro_medico');
                    const diagnosticoInput = document.getElementById('diagnostico');
                    insertInternamiento(id, fechaInput.value, centroMedicoInput.value, diagnosticoInput.value);
                });
            });

            const loadPersonas = async () => {
                cache = await fetch('api.php?ruta=personas').then(response => response.json())
                persona = cache.find(p => parseInt(p.id) === id);
            }

            const insertInternamiento = (id, fecha, centro_medico, diagnostico) => {
                fetch(`api.php?ruta=internamientos`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        persona_id: id,
                        fecha: fecha,
                        centro_medico: centro_medico,
                        diagnostico: diagnostico
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.message === 'success') {
                            location.reload();
                        } else {
                            alert('Error al añadir el internamiento: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al añadir el internamiento');
                    });
            }

            const fetchInternamientos = async (id) => {
                const internamientosList = document.getElementById('internamientos-list');
                const internamientos = await fetch(`api.php?ruta=internamientos&persona_id=${id}`).then(response => response.json());

                if (!internamientos || internamientos.length === 0) {
                    internamientosList.innerHTML = '<tr><td colspan="4">No hay internamientos registrados</td></tr>';
                    return;
                }

                internamientosList.innerHTML = '';
                internamientos.forEach(internamiento => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${internamiento.fecha}</td>
                        <td>${internamiento.centro_medico}</td>
                        <td>${internamiento.diagnostico}</td>
                        <td>
                            <a href="javascript:void(0)" class="delete-btn" data-id="${internamiento.id}">Eliminar</a>
                        </td>
                    `;
                    internamientosList.appendChild(row);
                    setRemoveListeners(row.querySelector('.delete-btn'));
                });
            }

            const setRemoveListeners = (btn) => {
                btn.addEventListener('click', function () {
                    if (confirm('¿Estás seguro de que deseas eliminar este internamiento?')) {
                        fetch(`api.php?ruta=internamientos&method=delete&id=${this.dataset.id}`, {
                            method: 'POST'
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.message === 'success') {
                                    location.reload();
                                } else {
                                    alert('Error al eliminar');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Error al eliminar');
                            });
                    }
                });
            }
        </script>
    </body>
</html>