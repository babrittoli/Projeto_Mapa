<?php
// Impede o acesso direto ao script via URL direto
defined('BASEPATH') or exit('No direct script access allowed');

//Model M_sala: Responsável pela interação com a tabela 'salas' no banco de dados.
class M_sala extends CI_Model
{
    /*
    TABELA DE CÓDIGOS DE RETORNO:
    0 - Erro crítico (Exceção)
    1 - Sucesso total
    8 - Falha na operação (Banco de dados não alterado)
    9 - Registro existe mas está desativado
    10 - Registro já existe (Duplicidade)
    98 - Consulta sem resultados
    */

    /**
     * Função para inserir uma nova sala
     */
    public function inserir($codigo, $descricao, $andar, $capacidade) {
        try {
            // Primeiro, chama o método auxiliar para checar se o código da sala já existe
            $retornoConsulta = $this->consultaSala($codigo);

            // Só prossegue com a inserção se o código NÃO for 9 (desativada) e NÃO for 10 (já ativa)
            if ($retornoConsulta['codigo'] != 9 && $retornoConsulta['codigo'] != 10) {

                // Prepara a instrução SQL de inserção com os valores recebidos por parâmetro
                $sql = "insert into Salas (codigo, descricao, andar, capacidade) 
                        values ($codigo, '$descricao', $andar, $capacidade)";
                
                // Executa a consulta sql no banco de dados
                $this->db->query($sql);

                // Verifica se o banco de dados realmente sofreu alteração (se a linha foi inserida)
                if ($this->db->affected_rows() > 0) {
                    $dados = array(
                        'codigo' => 1,
                        'msg' => 'Sala cadastrada corretamente'
                    );
                } else {
                    // Caso a query execute mas nenhuma linha seja afetada
                    $dados = array(
                        'codigo' => 8,
                        'msg' => 'Houve algum problema na inserção na tabela de salas.'
                    );
                }
            } else {
                // Se a sala já existia (ativa ou desativada), repassa o erro vindo da consulta
                $dados = array(
                    'codigo' => $retornoConsulta['codigo'],
                    'msg' => $retornoConsulta['msg']
                );
            }
        } catch (Exception $e) {
            // Captura erros inesperados do PHP ou do banco de dados
            $dados = array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }
        
        // Retorna o array de status para o Controller
        return $dados; 
    }

    //Método privado (auxiliar): Verifica a existência de uma sala pelo código
    private function consultaSala($codigo) {
        try {
            // Define a query de busca pelo código único da sala
            $sql = "select * from Salas where codigo = $codigo ";

            // Executa a consulta
            $retornoSala = $this->db->query($sql);

            // Verifica se a consulta retornou pelo menos uma linha (registro encontrado)
            if ($retornoSala->num_rows() > 0) {
                // Transforma o resultado da query em um objeto para acessar os campos
                $linha = $retornoSala->row();

                // Verifica o campo 'estatus'. Se for "D" (Desativado), retorna erro 9
                if (trim($linha->estatus) == "D") {
                    $dados = array(
                        'codigo' => 9,
                        'msg' => 'Sala desativada no sistema, fale com o administrador.'
                    );
                } else {
                    // Se estiver ativa, retorna erro 10 (Sala já cadastrada)
                    $dados = array(
                        'codigo' => 10,
                        'msg' => 'Sala já cadastrada no sistema.'
                    );
                }
            } else {
                // Se não encontrou nada, retorna o código 98 que pode inserir algo - Consulta sem resultados
                $dados = array(
                    'codigo' => 98,
                    'msg' => 'Sala não encontrada.'
                );
            }
        } catch (Exception $e) {
            // Tratamento de erro na consulta
            $dados = array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }

        // Retorna o resultado da análise para a função que a chamou
        return $dados;
    }
}
?>