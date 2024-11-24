<?php

header('Content-Type: application/json');
$menuFile = 'menu.json';

function obtenerMenu()
{
    global $menuFile;
    if (!file_exists($menuFile)) {
        return [];
    }
    $jsonData = file_get_contents($menuFile);
    return json_decode($jsonData, true);
}

function guardarMenu($data)
{
    global $menuFile;
    $jsonData = json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($menuFile, $jsonData);
}

function generarNuevoId($data)
{
    $maxId = 0;
    foreach ($data as $item) {
        if ($item['id'] > $maxId) {
            $maxId = $item['id'];
        }
    }
    return $maxId + 1;
}

function validarEnlace($enlace)
{
    return filter_var($enlace, FILTER_VALIDATE_URL);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $accion = $input['accion'];
    $item = $input['item'];
    $menu = obtenerMenu();

    if ($accion !== 'eliminar' && !validarEnlace($item['enlace'])) {
        http_response_code(400);
        echo json_encode(['estado' => 'error', 'mensaje' => 'El enlace no es valido']);
        exit;
    }

    switch ($accion) {
        case 'agregar':
            $item['id'] = generarNuevoId($menu);
            $menu[] = $item;
            break;
        case 'actualizar':
            foreach ($menu as &$menuItem) {
                if ($menuItem['id'] === $item['id']) {
                    $menuItem = $item;
                    break;
                }
            }
            break;
        case 'eliminar':
            $menu = array_filter($menu, fn ($menuItem) => $menuItem['id'] !== $item['id']);
            break;
    }

    guardarMenu($menu);
    echo json_encode(['estado' => 'success', 'menu' => $menu]);
    exit;
}

echo json_encode(obtenerMenu());
