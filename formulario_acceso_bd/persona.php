<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Agregar/Editar Persona - Ramón Perdomo</title>
        <link rel="stylesheet" href="assets/style.css">
    </head>
    <body>
        <div class="card">
            <h4>Agregar/Editar Persona</h4>
            <form id="persona-form">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>

                <label for="edad">Edad:</label>
                <input type="number" id="edad" name="edad" required>

                <label for="genero">Género:</label>
                <select id="genero" name="genero" required>
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
                </select>

                <button type="button" onclick="history.back()">Atrás</button>
                <button type="submit">Guardar</button>
            </form>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const params = new URLSearchParams(window.location.search);
                const id = params.get('id');

                if (id) {
                    fetchData(`api.php?ruta=personas&id=${id}`, null, 'GET')
                        .then(data => {
                            document.getElementById('nombre').value = data.nombre;
                            document.getElementById('edad').value = data.edad;
                            document.getElementById('genero').value = data.genero;
                        })
                        .catch(error => alert(error.message));
                }

                document.getElementById('persona-form').addEventListener('submit', function (event) {
                    event.preventDefault();
                    const data = getFormData();
                    const url = id ? `api.php?ruta=personas&method=update&id=${id}` : 'api.php?ruta=personas';
                    fetchData(url, data)
                        .then(() => window.location.href = 'index.php')
                        .catch(error => alert(error.message));
                });

                function getFormData() {
                    return {
                        nombre: document.getElementById('nombre').value,
                        edad: document.getElementById('edad').value,
                        genero: document.getElementById('genero').value
                    };
                }

                function fetchData(url, data = null, method = 'POST') {
                    const options = {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    };

                    if (data) {
                        options.body = JSON.stringify(data);
                    }

                    return fetch(url, options)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Error en la solicitud: ' + response.statusText);
                            }

                            return response.json();
                        });
                }
            });
        </script>
    </body>
</html>