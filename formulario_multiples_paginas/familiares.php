<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Familiares - Ramón Perdomo</title>
        <link rel="stylesheet" href="assets/style.css">
    </head>
    <body>
        <div class="card">
            <h4>Familiares</h4>
            <h5>Nombre: <span id="nombre"></span></h5>
            <form id="add_familiar" style="text-align: center">
                <div style="display: flex; justify-content: center">
                    <select name="parentesco" id="parentesco" style="margin-right: 10px" required>
                        <option value="" disabled selected>Parentesco</option>
                        <option value="pareja">Pareja</option>
                        <option value="padre">Padre</option>
                        <option value="madre">Madre</option>
                        <option value="hijo">Hijo</option>
                        <option value="hermano">Hermano</option>
                        <option value="abuelo">Abuelo</option>
                        <option value="tio">Tio</option>
                        <option value="primo">Primo</option>
                        <option value="sobrino">Sobrino</option>
                    </select>
                    <select name="familiar_id" id="familiar_id" required>
                        <option value="" disabled selected>Cargando personas...</option>
                    </select>
                </div>
                <a href="persona.php">
                    <button class="add-btn" type="button">Añadir Nueva Persona</button>
                </a>
                <button class="add-btn" type="submit">Añadir Nuevo Familiar</button>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Parentesco</th>
                        <th>Edad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="familiares-list">
                    <tr>
                        <td colspan="4">Cargando...</td>
                    </tr>
                </tbody>
            </table>
            <br>
            <button type="button" onclick="history.back()">Atrás</button>
            <a href="enfermedades.php?id=<?= $_GET['id'] ?? ''; ?>">
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
                    fetchFamiliares(id);
                } else {
                    window.location.href = 'index.php';
                }

                const addFamiliarForm = document.getElementById('add_familiar');
                addFamiliarForm.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const parentescoSelect = document.getElementById('parentesco');
                    const familiarIdSelect = document.getElementById('familiar_id');
                    insertFamiliar(id, familiarIdSelect.value, parentescoSelect.value);
                });
            });

            const loadPersonas = async () => {
                const personas = cache = await fetch('api.php?ruta=personas').then(response => response.json())
                persona = cache.find(p => parseInt(p.id) === id);

                let hayPersonas = false;
                const familiarIdSelect = document.getElementById('familiar_id');
                familiarIdSelect.innerHTML = '';

                personas.forEach(p => {
                    // Evitar que se pueda seleccionar la misma 
                    // persona o que ya esté añadida
                    if (parseInt(p.id) === parseInt(persona.id) ||
                        (persona.familiares && persona.familiares.find(f => parseInt(f.id) === parseInt(p.id)))) {
                        return;
                    }

                    const option = document.createElement('option');
                    option.value = p.id;
                    option.textContent = p.nombre;
                    familiarIdSelect.appendChild(option);
                    hayPersonas = true;
                });

                if (!hayPersonas) {
                    familiarIdSelect.innerHTML = '<option value="" disabled selected>No hay personas disponibles</option>';
                }
            }

            const insertFamiliar = (id, familiarId, parentesco) => {
                fetch(`api.php?ruta=personas`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id,
                        familiar_id: familiarId,
                        parentesco: parentesco
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.id !== undefined) {
                            location.reload();
                        } else {
                            alert('Error al añadir el familiar: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al añadir el familiar');
                    });
            }

            const fetchFamiliares = (id) => {
                const familiaresList = document.getElementById('familiares-list');

                if (!persona.familiares || persona.familiares.length === 0) {
                    familiaresList.innerHTML = '<tr><td colspan="4">No hay familiares registrados</td></tr>';
                    return;
                }

                familiaresList.innerHTML = '';

                persona.familiares.forEach(async persona => {
                    const row = document.createElement('tr');
                    const familiar = cache.find(p => parseInt(p.id) === parseInt(persona.id));

                    row.innerHTML = `
                        <td>${familiar.nombre}</td>
                        <td>${persona.parentesco}</td>
                        <td>${familiar.edad}</td>
                        <td>
                            <a href="persona.php?id=${familiar.id}" class="edit-btn">Editar</a>
                            <a href="javascript:void(0)" class="delete-btn" data-id="${id}" data-familiar-id="${familiar.id}">Eliminar</a>
                        </td>
                    `;

                    familiaresList.appendChild(row);
                    setRemoveListeners(row.querySelector('.delete-btn'));
                });
            }

            const setRemoveListeners = (btn) => {
                btn.addEventListener('click', function () {
                    if (confirm('¿Estás seguro de que deseas eliminar este familiar?')) {
                        fetch(`api.php?ruta=personas&method=delete&id=${this.dataset.id}&familiar_id=${this.dataset.familiarId}`, {
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