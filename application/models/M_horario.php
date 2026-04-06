<?php
// Impede o acesso direto ao script para aumentar a segurança
defined('BASEPATH') or exit('No direct script access allowed');

// Define a classe M_horario que estende as funcionalidades do Model padrão do CodeIgniter
class M_horario extends CI_Model
{
    /*
    Validação dos tipos de retornos nas validações (Código de erro)
    0 - Erro de exceção
    1 - Operação realizada no banco de dados com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    8 - Houve algum problema de inserção, atualização, consulta ou exclusão
    9 - Horário desativado no sistema
    10 - Horário já cadastrado
    11 - Horário não encontrado pelo método público
    98 - Método auxiliar de consulta que não trouxe dados
    */

    // Função pública para inserir um novo horário no banco de dados
    public function inserir($descricao, $horaInicial, $horaFinal){
        try {
            // Chama o método privado interno para verificar se o horário já existe no banco
            $retornoConsulta = $this->consultarHorario('', $horaInicial, $horaFinal);

            // Verifica se o código retornado pela consulta NÃO é 9 (desativado) e NÃO é 10 (já cadastrado)
            if ($retornoConsulta['codigo'] != 9 && $retornoConsulta['codigo'] != 10) {

                // Define a query SQL de inserção dos dados na tabela 'horarios'
                $this->db->query("insert into horarios (descricao, hora_inicial, hora_final) 
                                 values ('$descricao', '$horaInicial', '$horaFinal')");

                // Verifica se o número de linhas afetadas no banco de dados é maior que zero (sucesso)
                if ($this->db->affected_rows() > 0) {
                    $dados = array(
                        'codigo' => 1, // Código de sucesso
                        'msg' => 'Horário cadastrado corretamente.'
                    );
                } else {
                    // Caso a query rode mas não insira nada (ex: erro de banco)
                    $dados = array(
                        'codigo' => 8,
                        'msg' => 'Houve algum problema na inserção na tabela de horários.'
                    );
                }
            } else {
                // Se o horário já estiver cadastrado ou desativado, repassa o erro da consulta
                $dados = array(
                    'codigo' => $retornoConsulta['codigo'],
                    'msg' => $retornoConsulta['msg']
                );
            }
        } catch (Exception $e) {
            // Captura qualquer erro inesperado (exceção) e retorna a mensagem técnica
            $dados = array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }

        // Retorna o array $dados com o resultado da operação para o Controller
        return $dados;
    }

    // Método privado auxiliar para buscar horários por código ou por intervalo de horas
    private function consultarHorario($codigo, $horaInicial, $horaFinal){
        try {
            // Verifica se foi passado um código específico para a busca
            if($codigo != ''){
                // Busca o registro pelo ID (código)
                $sql = "select * from horarios where codigo = $codigo ";
            } else {
                // Se não houver código, busca pela coincidência de horário inicial e final
                $sql = "select * from horarios 
                        where hora_inicial = '$horaInicial' 
                        and hora_final = '$horaFinal'";
            }

            // Executa a query montada acima
            $retornoHorario = $this->db->query($sql);

            // Verifica se a consulta retornou pelo menos uma linha (registro encontrado)
            if($retornoHorario->num_rows() > 0){
                // Obtém a primeira linha do resultado
                $linha = $retornoHorario->row();

                // Verifica se o status do horário é "D" (Desativado), removendo espaços em branco
                if (trim($linha->estatus) == "D") {
                    $dados = array(
                        'codigo' => 9,
                        'msg' => 'Horário desativado no sistema, caso precise reativar o mesmo, fale com o administrador.'
                    );
                } else {
                    // Se o registro existe e não está desativado, ele já está cadastrado e ativo
                    $dados = array(
                        'codigo' => 10,
                        'msg' => 'Horário já cadastrado no sistema.'
                    );
                }
            } else {
                // Caso a query não encontre nenhum registro correspondente
                $dados = array(
                    'codigo' => 98,
                    'msg' => 'Horário não encontrado.'
                );
            }
        } catch (Exception $e) {
            // Captura erros durante a execução da consulta
            $dados = array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }

        // Retorna o resultado da consulta para quem chamou o método
        return $dados;
    }

    // Método público para buscar horários com base em múltiplos filtros
    public function consultar($codigo, $descricao, $horaInicial, $horaFinal){
        try {
            // Inicializa a query buscando apenas registros onde o status é vazio (ativos)
            $sql = "select * from horarios where estatus = '' ";

            // Se o código foi informado, adiciona o filtro 'and codigo = ...'
            if (trim($codigo) != '') {
                $sql = $sql . " and codigo = $codigo ";
            }

            // Se a descrição foi informada, adiciona busca parcial usando LIKE
            if (trim($descricao) != '') {
                $sql = $sql . " and descricao like '%$descricao%' ";
            }

            // Filtra pelo horário de início, se fornecido
            if (trim($horaInicial) != '') {
                $sql = $sql . " and hora_inicial = '$horaInicial' ";
            }

            // Filtra pelo horário de término, se fornecido
            if (trim($horaFinal) != '') {
                $sql = $sql . " and hora_final = '$horaFinal' ";
            }

            // Adiciona ordenação padrão pelo código
            $sql = $sql . " order by codigo";

            // Executa a query montada dinamicamente
            $retorno = $this->db->query($sql);

            // Se encontrar resultados, retorna os dados em um array de objetos
            if ($retorno->num_rows() > 0) {
                $dados = array(
                    'codigo' => 1,
                    'msg' => 'Consulta efetuada com sucesso.',
                    'dados' => $retorno->result() // Contém a lista de registros
                );
            } else {
                // Caso não encontre nenhum registro com esses filtros
                $dados = array(
                    'codigo' => 11,
                    'msg' => 'Horário não encontrado.'
                );
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 00,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }

        return $dados;
    }

    // Método público para atualizar os dados de um horário existente
    public function alterar($codigo, $descricao, $horaInicial, $horaFinal){
        try {
            // Verifica se o código informado realmente existe no banco antes de tentar atualizar
            $retornoConsulta = $this->consultar($codigo, '', '', '');

            // Se o retorno for 1, o registro existe
            if ($retornoConsulta['codigo'] == 1) {
                // Inicia a montagem da query de update
                $query = "update horarios set ";

                // Se a nova descrição não for vazia, adiciona ao update
                if ($descricao !== '') {
                    $query .= "descricao = '$descricao', ";
                }

                // Se a nova hora inicial não for vazia, adiciona ao update
                if ($horaInicial !== '') {
                    $query .= "hora_inicial = '$horaInicial', ";
                }

                // Se a nova hora final não for vazia, adiciona ao update
                if ($horaFinal !== '') {
                    $query .= "hora_final = '$horaFinal', ";
                }

                // Remove a última vírgula sobressalente e adiciona a cláusula WHERE
                $queryFinal = rtrim($query, ", ") . " where codigo = $codigo";

                // Executa a atualização no banco de dados
                $this->db->query($queryFinal);

                // Verifica se alguma linha foi alterada
                if ($this->db->affected_rows() > 0) {
                    $dados = array(
                        'codigo' => 1,
                        'msg' => 'Horário atualizado corretamente.'
                    );
                } else {
                    $dados = array(
                        'codigo' => 8,
                        'msg' => 'Houve algum problema na atualização na tabela de horário.'
                    );
                }
            } else {
                // Se o registro não for encontrado para alteração
                $dados = array(
                    'codigo' => $retornoConsulta['codigo'],
                    'msg' => $retornoConsulta['msg']
                );
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }

        return $dados;
    }

    // Método público para realizar a desativação lógica (soft delete)
    public function desativar($codigo){
        try {
            // Verifica se o horário existe através do método auxiliar (retorno 10 indica que existe e está ativo)
            $retornoConsulta = $this->consultarHorario($codigo, '', '');

            if ($retornoConsulta['codigo'] == 10) {
                // Altera o campo estatus para 'D' (Desativado)
                $this->db->query("update horarios set estatus = 'D' where codigo = $codigo");

                // Verifica se a atualização foi bem sucedida
                if ($this->db->affected_rows() > 0) {
                    $dados = array(
                        'codigo' => 1,
                        'msg' => 'Horário DESATIVADO corretamente.'
                    );
                } else {
                    $dados = array(
                        'codigo' => 8,
                        'msg' => 'Houve algum problema na DESATIVAÇÃO do Horário.'
                    );
                }
            } else {
                // Se o horário já estiver desativado ou não existir
                $dados = array(
                    'codigo' => $retornoConsulta['codigo'],
                    'msg' => $retornoConsulta['msg']
                );
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }
        return $dados;
    }
}