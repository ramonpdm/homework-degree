<?php

header('Content-Type: application/json');

function validarCedula(string $cedula): bool
{
    // Remover guiones o espacios de la cédula
    $cedula = str_replace('-', '', trim($cedula));

    if (strlen($cedula) !== 11) {
        return false;
    }

    // El dv sería el último dígito de la cédula
    $digitoVerificador = intval($cedula[10]);

    $suma = 0;

    // Recorremos la cédula de derecha a izquierda
    for ($i = 0; $i < 10; $i++) {
        // Convertir la letra en número
        $digito = intval($cedula[$i]);

        // Alternar entre multiplicar por 1 y por 2
        // viendo si el índice es par o impar
        if (($i % 2) === 0) {
            $digito *= 1;
        } else {
            $digito *= 2;
        }

        // Si el resultado de la multiplicación es mayor
        // que 9, sumamos los dígitos del resultado, por
        // ejemplo, 12 sería 1 + 2 = 3
        if ($digito > 9) {
            $digito = floor($digito / 10) + ($digito % 10);
        }

        $suma += $digito;
    }

    // Calcular el residuo de la suma
    $residuo = $suma % 10;

    // Si el residuo es diferente de 0, restar de
    // 10 para obtener el dígito calculado
    $digitoCalculado = ($residuo === 0) ? 0 : 10 - $residuo;

    // Comparar el dígito calculado con el dígito verificador
    return $digitoCalculado === $digitoVerificador;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['cedula'])) {
    $cedula = $_GET['cedula'];

    if (validarCedula($cedula)) {
        $resultado = [
            'cedula' => $cedula,
            'valida' => true,
            'mensaje' => "La cédula '$cedula' es correcta!"
        ];
    } else {
        $resultado = [
            'cedula' => $cedula,
            'valida' => false,
            'mensaje' => "La cédula '$cedula' es inválida"
        ];
    }

    echo json_encode($resultado);
} else {
    echo json_encode([
        'error' => 'Método no soportado o parámetro cédula no proporcionado'
    ]);
}