<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Condiciones de Salud - Ramón Perdomo</title>
        <link rel="stylesheet" href="assets/style.css">
    </head>
    <body>
        <div class="card">
            <h4>Condiciones de Salud</h4>
            <h5>Nombre: <span id="nombre"></span></h5>
            <form id="add_enfermedad" style="text-align: center">
                <div style="display: flex; justify-content: center">
                    <input type="text" style="margin-right: 5px" name="enfermedad" id="enfermedad" placeholder="Enfermedad" required>
                    <input type="text" style="margin-right: 5px" name="tiempo" id="tiempo" placeholder="Tiempo con ella" required>
                    <input type="text" style="margin-right: 5px" name="detalles" id="detalles" placeholder="Detalles" required>
                </div>
                <button class="add-btn" type="submit">Añadir Nueva Enfermedad</button>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>Enfermedad</th>
                        <th>Tiempo con la Enfermedad</th>
                        <th>Detalles</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="enfermedades-list">
                    <tr>
                        <td colspan="4">Cargando...</td>
                    </tr>
                </tbody>
            </table>
            <br>
            <button type="button" onclick="history.back()">Atrás</button>
            <a href="internamientos.php?id=<?= $_GET['id'] ?>">
                <button type="button">Siguiente</button>
            </a>
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
                    await fetchEnfermedades(id);
                } else {
                    window.location.href = 'index.php';
                }

                const addEnfermedadForm = document.getElementById('add_enfermedad');
                addEnfermedadForm.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const enfermedadInput = document.getElementById('enfermedad');
                    const tiempoInput = document.getElementById('tiempo');
                    const detallesInput = document.getElementById('detalles');
                    insertEnfermedad(id, enfermedadInput.value, tiempoInput.value, detallesInput.value);
                });
            });

            const loadPersonas = async () => {
                cache = await fetch('api.php?ruta=personas').then(response => response.json())
                persona = cache.find(p => parseInt(p.id) === id);
            }

            const insertEnfermedad = (id, enfermedad, tiempo, detalles) => {
                fetch(`api.php?ruta=enfermedades`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        persona_id: id,
                        enfermedad: enfermedad,
                        tiempo: tiempo,
                        detalles: detalles
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.message === 'success') {
                            location.reload();
                        } else {
                            alert('Error al añadir la enfermedad: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al añadir la enfermedad');
                    });
            }

            const fetchEnfermedades = async (id) => {
                const enfermedadesList = document.getElementById('enfermedades-list');
                const enfermedades = await fetch(`api.php?ruta=enfermedades&persona_id=${id}`).then(response => response.json());

                if (!enfermedades || enfermedades.length === 0) {
                    enfermedadesList.innerHTML = '<tr><td colspan="4">No hay enfermedades registradas</td></tr>';
                    return;
                }

                enfermedadesList.innerHTML = '';
                enfermedades.forEach(enfermedad => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${enfermedad.nombre}</td>
                        <td>${enfermedad.tiempo}</td>
                        <td>${enfermedad.detalles}</td>
                        <td>
                            <a href="javascript:void(0)" class="delete-btn" data-id="${enfermedad.id}">Eliminar</a>
                        </td>
                    `;
                    enfermedadesList.appendChild(row);
                    setRemoveListeners(row.querySelector('.delete-btn'));
                });
            }

            const setRemoveListeners = (btn) => {
                btn.addEventListener('click', function () {
                    if (confirm('¿Estás seguro de que deseas eliminar esta enfermedad?')) {
                        fetch(`api.php?ruta=enfermedades&method=delete&id=${this.dataset.id}`, {
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