<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Personas - Ramón Perdomo</title>
        <link rel="stylesheet" href="assets/style.css">
    </head>
    <body>
        <div class="card">
            <h4>Personas</h4>
            <a href="persona.php" class="add-btn">Añadir Nuevo</a>
            <table id="personas">
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Edad</th>
                        <th>Género</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="4" style="text-align: center">Cargando...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const table = document.getElementById('personas');
                const tbody = table.querySelector('tbody');

                fetch('api.php?ruta=personas')
                    .then(response => response.json())
                    .then(data => {
                        if (data.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center">No hay personas registradas</td></tr>';
                            return;
                        }

                        tbody.innerHTML = '';
                        data.forEach(persona => renderPerson(persona));
                        setRemoveListeners();
                    })
                    .catch(error => console.error('Error al cargar los datos:', error));

                const renderPerson = (persona) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${persona.nombre}</td>
                        <td>${persona.edad}</td>
                        <td>${persona.genero}</td>
                        <td>
                            <a href="persona.php?id=${persona.id}" class="edit-btn">Editar</a>
                            <a href="javascript:void(0)" class="delete-btn" data-id="${persona.id}">Eliminar</a>
                        </td>
                    `;
                    tbody.appendChild(tr);
                }

                const setRemoveListeners = () => {
                    document.querySelectorAll('.delete-btn').forEach(btn => {
                        btn.addEventListener('click', function () {
                            if (confirm('¿Estás seguro de que deseas eliminar esta persona?')) {
                                fetch(`api.php?ruta=personas&id=${this.dataset.id}&method=delete`, {
                                    method: 'POST'
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.message === 'success') {
                                            location.reload();
                                        } else {
                                            alert('Error al eliminar');
                                        }
                                    });
                            }
                        });
                    });
                }
            });
        </script>
    </body>
</html>
