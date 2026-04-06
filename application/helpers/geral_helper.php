<?php
    // Garatindo que esse arquivo não seja acessado direto pelo navegador
    // Ele só pode ser usado dentro do CodeIgniter
    defined('BASEPATH') or exit('Acesso ao script não permitido');

    // Criando a função pra validar se os parâmetros que vêm do Front estão corretos
    // $atributos: são os dados que chegaram 
    // $lista: são os campos que eu espero receber (tipo: nome, email, etc, que tenho lá no front)
    function verificarParametros($atributos, $lista){

        // Aqui eu percorro todos os campos que eu estou esperando
        foreach($lista as $key => $value){
            
            // Converto o objeto em array e verifico se a chave existe
            // Ex: se estou esperando "nome", vejo se realmente veio "nome"
            if(array_key_exists($key, get_object_vars($atributos))){
                
                // Se encontrou o campo, sigo o fluxo normalmente, só verifica se ele existe nao que esta correto, preencheu certo
                $estatus = 1;
            
            } else {
                
                // se não encontrou o campo, ja vai dar erro
                $estatus = 0;
                
                // paro o loop porque não precisa continuar validando porque ja encontrei um problema
                break;
            }
        }


        // verificando se a quantidade de campos recebidos é exatamente a mesma que eu esperava
        if(count(get_object_vars($atributos)) != count($lista)){
            
            // se a quantidade for diferente, considero inválido
            $estatus = 0;
        }

        // aqui eu retorno o resultado final da validacao
        // se for 1 todos os campos existem e estão na quantidade certa
        // se forfaltou campo ou veio algo errado
        return $estatus;
    }

    // Definindo a funcao chamada validarDados
    // a função recebe: $valor  que será validado
    // $tipo: o tipo esperado (int, string, date, hora)
    // $tamanhoZero: define se o valor 0 deve ser considerado inválido (true (verdadeiro) por padrão)
    function validarDados($valor, $tipo, $tamanhoZero = true) {

        // Verifica se o valor é nulo ou vazio
        if (is_null($valor) || $valor === '') {
            // Retorna erro dizendo que está vazio ou nulo
            return array('codigoHelper' => 2, 'msg' => 'Conteúdo nulo ou vazio.');
        }

        // Verifica se deve considerar o valor 0 como inválido
        if ($tamanhoZero && ($valor === 0 || $valor === '0')) {
            // Retorna erro dizendo que o conteúdo está zerado
            return array('codigoHelper' => 3, 'msg' => 'Conteúdo zerado.');
        }

        // Estrutura que decide o que fazer dependendo do tipo informado
        switch ($tipo) {

            // caso o tipo seja inteiro
            case 'int':
                // verifica se o valor é um número inteiro válido
                // filter_var usada para validar ou sanitizar (limpar) dados
                if (filter_var($valor, FILTER_VALIDATE_INT) === false) {
                    // Retorna erro se não for inteiro
                    return array('codigoHelper' => 4, 'msg' => 'Conteúdo não inteiro.');
                }
            break;

            // Caso o tipo seja string (texto)
            case 'string':
                // Verifica se é uma string e se não está vazia depois de tirar espaços
                if (!is_string($valor) || trim($valor) === '') {
                    // Retorna erro se não for texto válido
                    return array('codigoHelper' => 5, 'msg' => 'Conteúdo não é um texto.');
                }
            break;

            // Caso o tipo seja data
            case 'date':
                // Verifica se está no formato ano, mes e dia 
                if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $valor, $match)) {
                    // Retorna erro se o formato estiver errado
                    return array('codigoHelper' => 6, 'msg' => 'Data em formato inválido.');
                } else {
                    // Tenta criar uma data real usando esse formato
                    $d = DateTime::createFromFormat('Y-m-d', $valor);

                    // Verifica se a data realmente existe 
                    if (($d->format('Y-m-d') === $valor) == false) {
                        // Retorna erro se a data não for válida
                        return array('codigoHelper' => 6, 'msg' => 'Data inválida.');
                    }
                }
            break;

            // Caso o tipo seja hora
            case 'hora':
                // Verifica se está no formato HH:MM (ex: 23:59)
                if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $valor)) {
                    // Retorna erro se o formato estiver errado
                    return array('codigoHelper' => 7, 'msg' => 'Hora em formato inválido.');
                }
            break;

            // Caso o tipo não seja nenhum dos acima
            default:
                // Retorna erro dizendo que o tipo não foi definido
                return array('codigoHelper' => 0, 'msg' => 'Tipo de dado não definido.');
        }

        // Se passou por tudo sem erro, retorna sucesso
        return array('codigoHelper' => 0, 'msg' => 'Validação correta.');
    }

    // Função para verificar os tipos de dados para Consulta
    function validarDadosConsulta($valor, $tipo) { // Define a função que recebe um valor e o tipo esperado

        if ($valor != '') { // Verifica se o valor não está vazio
            switch ($tipo) { // Escolhe o tipo de validação com base no tipo informado

                case 'int': // Caso o tipo seja inteiro
                    // Filtra como inteiro, aceita '123' ou 123
                    if (filter_var($valor, FILTER_VALIDATE_INT) === false) { // Valida se é um inteiro válido
                        return array('codigoHelper' => 4, 'msg' => 'Conteúdo não inteiro.'); // Retorna erro se não for inteiro
                    }
                    break; // Encerra o case

                case 'string': // Caso o tipo seja string
                    // Garante que é string não vazia após trim
                    if (!is_string($valor) || trim($valor) === '') { // Verifica se é string e não está vazia após remover espaços
                        return array('codigoHelper' => 5, 'msg' => 'Conteúdo não é um texto.'); // Retorna erro se inválido
                    }
                    break; // Encerra o case

                case 'date': // Caso o tipo seja data
                    // Verifico se tem padrão de data
                    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $valor, $match)) { // Verifica formato YYYY-MM-DD com regex
                        return array('codigoHelper' => 6, 'msg' => 'Data em formato inválido.'); // Retorna erro se formato estiver errado
                    } else {
                        // Tenta criar DateTime no formato Y-m-d
                        $d = DateTime::createFromFormat('Y-m-d', $valor); // Cria objeto DateTime com o valor informado

                        if (($d->format('Y-m-d') !== $valor) || $d === false) { // Confere se a data gerada é igual à original
                            return array('codigoHelper' => 6, 'msg' => 'Data inválida.'); // Retorna erro se a data não for válida
                        }
                    }
                    break; // Encerra o case

                case 'hora': // Caso o tipo seja hora
                    // Verifico se tem padrão de hora
                    if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $valor)) { // Valida formato HH:MM (00:00 até 23:59)
                        return array('codigoHelper' => 7, 'msg' => 'Hora em formato inválido.'); // Retorna erro se inválido
                    }
                    break; // Encerra o case

                default: // Caso o tipo não seja reconhecido
                    return array('codigoHelper' => 97, 'msg' => 'Tipo de dado não definido.'); // Retorna erro de tipo desconhecido
            }
        }

        // Valor default da variável retorno caso não ocorra erro
        return array('codigoHelper' => 0, 'msg' => 'Validação correta.'); // Retorna sucesso
    }    
    
?>
