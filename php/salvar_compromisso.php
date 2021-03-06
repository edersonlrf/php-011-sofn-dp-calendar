<?php

header('Content-Type: application/json');

require "../vendor/autoload.php";

require 'conexao.php';

$nome = $_POST['nome'] ?? null;
$inicio = $_POST['inicio'] ?? null;
$fim = $_POST['fim'] ?? null;

if (!empty($nome) && !empty($inicio) && !empty($fim)) {

    $sql = "INSERT INTO agendamentos (nome, inicio, fim)
        VALUES (:nome, :inicio, :fim);";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':inicio', $inicio);
    $stmt->bindParam(':fim', $fim);

    $res = $stmt->execute();

    $retorno = array();

    if ($res) {
        $retorno['status'] = 'success';
        $retorno['mensagem'] = "Agendamento de paciente {$nome} foi salvo com sucesso!";

        $retorno['id'] = $conn->lastInsertId();
        $retorno['title'] = $nome;
        $retorno['start'] = $inicio;
        $retorno['end'] = $fim;

        $options = [
            'cluster' => 'us2'
            // 'encrypted' => true
        ];

        $pusher = new Pusher\Pusher(
            'dff85a9b2c9686275a8b',
            '436749a9225746a643ba',
            '567265',
            $options
        );

        $response['nome'] = $nome;
        $response['dia'] = date("d/m/Y", strtotime($inicio));
        $response['hora'] = date("H:i", strtotime($inicio));

        $pusher->trigger('my-channel', 'my-event', $response);
    } else {
        $retorno['status'] = 'fail';
        $retorno['mensagem'] = 'Ocorreu algum erro ao gravar compromisso.';
    }

} else {
    $retorno['status'] = 'fail';
    $retorno['mensagem'] = 'Informe um nome para o compromisso!';
}

echo json_encode($retorno);
exit;