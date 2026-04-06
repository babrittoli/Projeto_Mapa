<?php

defined('BASEPATH') OR exit('No direct script access allowed');
// Verifica se a constante BASEPATH está definida.
// Proteção do CodeIgniter para impedir acesso direto ao arquivo via URL, se alguém tentar acessar o arquivo diretamente, o script é encerrado.

class Sala extends CI_Controller {
// Criação da classe chamada Sala.
// "extends CI_Controller" significa que ela herda funcionalidades do controlador principal do CodeIgniter. (Herança)
// Ou seja, esta classe será um Controller da aplicação.


// Declarando para o Intelephense reconhecer o model
public M_sala $M_sala; 

    /*
    Validação dos tipos de retornos nas validações (Código de erro)
    1 - Operação realizada no banco de dados com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    2 - Conteúdo passado nulo ou vazio
    3 - Conteúdo zerado
    4 - Conteúdo não inteiro
    5 - Conteúdo não é um texto
    6 - Data em formato inválido
    7 - Hora em formato inválido
    12 - Na atualização, pelo menos 1 atributo deve ser passado. 
    99 - Parâmetros passados do front não correspondem ao método
    */

    //Atributos PRIVADOS da classe
    private $codigo;// Declara uma variável privada chamada codigo que só pode ser acessada dentro da própria classe.
    private $descricao; // Atributo privado que armazenará a descrição da sala.
    private $andar; // Atributo privado que armazenará o número do andar onde a sala está localizada.
    private $capacidade; // Atributo privado que armazenará a quantidade de pessoas que a sala suporta.
    private $estatus; // Atributo privado que indica o status da sala (ativa ou inativa).

    //Getters dos atributos para ACESSAR os valores privados
    public function getCodigo() { // Método público que permite acessar o valor da variável $codigo 
        return $this->codigo; // Retorna o valor armazenado no atributo codigo
    }

    public function getDescricao() {
        return $this->descricao; // Retorna o valor da descrição
    }

    public function getAndar() {
        return $this->andar; // Retorna o valor do andar
    }

    public function getCapacidade() {
        return $this->capacidade; // Retorna o valor da capacidade
    }

    public function getEstatus() {
        return $this->estatus; // Retorna o valor do estatus
    }

    //MÉTODO Setters dos atributos - Aonde DEFINE/ATRIBUI ou altera o valor de um atributo
    public function setCodigo($codigoFront) { //Método público utilizado para atribuir um valor ao atributo codigo. O valor vem do front-end e é recebido pelo parametro $codigoFront
        $this->codigo = $codigoFront; // Atribui o valor recebido do front-end a variavel privada $codigo da classe
    }

    public function setDescricao($descricaoFront) {
        $this->descricao = $descricaoFront; // Atribui o valor recebido do front-end ao atributo descricao da classe
    }
    
    public function setAndar($andarFront) {
        $this->andar = $andarFront;  // Atribui o valor recebido do front-end ao atributo andar da classe
    }

    public function setCapacidade($capacidadeFront)  { 
        $this->capacidade = $capacidadeFront; // Atribui o valor recebido do front-end ao atributo capacidade da classe
    }

    public function setEstatus($estatusFront) { 
        $this->estatus = $estatusFront;  // Atribui o valor recebido do front-end ao atributo da classe
    }

    // função para inserir dados da sala
    public function inserir() {

        $this->load->helper('geral_helper'); // para o codigo puxar a funcao da helper

        $erros = []; // Cria um array para armazenar possíveis erros durante o processo
        $sucesso = false; // Variável booleana que indicará se a operação foi bem-sucedida ou nao 

        try {
            // Lê os dados enviados pelo front-end no corpo da requisição enviados em formato JSON
            $json = file_get_contents('php://input');

            // Converte o JSON recebido em um objeto PHP
            $resultado = json_decode($json);

            // Cria um array com os campos esperados vindos do front-end
            $lista = [
                "codigo" => 0,
                "descricao" => 0,
                "andar" => 0,
                "capacidade" => 0
            ];

            // Verificando se os parâmetros recebidos correspondem aos esperados
            // Função auxiliar chamada verificarParam (helper)
            if (verificarParametros($resultado, $lista) != 1) {

                // Caso os parâmetros estejam incorretos ou não existam
                // adiciona um erro no array de erros
                $erros[] = [
                    'codigo' => 99,
                    'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'
                ];

            } else {
                // Validação dos dados recebidos verificando tipo e conteúdo. A Função validarDados está no helper
                $retornoCodigo = validarDados($resultado->codigo, 'int', true);  // Valida se o código é um inteiro válido              
                $retornoDescricao = validarDados($resultado->descricao, 'string', true); // Valida se a descrição é um texto (string)              
                $retornoAndar = validarDados($resultado->andar, 'int', true);  // Valida se o andar é um número inteiro                
                $retornoCapacidade = validarDados($resultado->capacidade, 'int', true); // Valida se a capacidade é um número inteiro
            
                // Verifica se houve erro na validação do campo código
                if($retornoCodigo['codigoHelper'] != 0){
                    // Caso o códigoHelper seja diferente de 0, significa que ocorreu algum erro, então adiciona um novo erro dentro do array $erros
                    $erros[] = [
                        'codigo' => $retornoCodigo['codigoHelper'], // código do erro retornado pelo helper
                        'campo' => 'Codigo', // nome do campo onde ocorreu o erro
                        'msg' => $retornoCodigo['msg'] // mensagem explicando o erro
                    ];
                }
                // Verifica se houve erro na validação da descrição
                if($retornoDescricao['codigoHelper'] != 0){
                    // Se existir erro, ele é adicionado ao array de erros
                    $erros[] = [
                        'codigo' => $retornoDescricao['codigoHelper'], // código do erro retornado
                        'campo' => 'Descricao', // campo que apresentou erro
                        'msg' => $retornoDescricao['msg'] // mensagem do erro
                    ];
                }
                // Verifica se houve erro na validação do andar
                if($retornoAndar['codigoHelper'] != 0){
                    // Se houver erro, adiciona ao array de erros
                    $erros[] = [
                        'codigo' => $retornoAndar['codigoHelper'], // código do erro
                        'campo' => 'Andar', // campo com erro
                        'msg' => $retornoAndar['msg'] // mensagem do erro
                    ];
                }
                // Verifica se houve erro na validação da capacidade
                if($retornoCapacidade['codigoHelper'] != 0){
                    // Caso exista erro, ele também será adicionado ao array $erros
                    $erros[] = [
                        'codigo' => $retornoCapacidade['codigoHelper'], // código do erro
                        'campo' => 'Capacidade', // campo onde ocorreu erro
                        'msg' => $retornoCapacidade['msg'] // mensagem descritiva do erro
                    ];
                }
                // Verifica se o array de erros está vazio, se nenhuma validação retornou erro
                if (empty($erros)) {
                    $this->setCodigo($resultado->codigo);  // Se não houver erros, o código recebido do frontend é atribuído ao atributo da classe usando o método setter
                    $this->setDescricao($resultado->descricao); // Define a descrição da sala usando o setter, o valor vem do objeto $resultado (dados recebidos do frontend)
                    $this->setAndar($resultado->andar);// Define o andar da sala usando o setter
                    $this->setCapacidade($resultado->capacidade);  // Define a capacidade da sala usando o setter

                    // Carrega o Model chamado M_sala. No CodeIgniter o Model é responsável pela comunicação com o banco de dados
                    $this->load->model('M_sala');

                    // Chamando o método inserir do model M_sala
                    // Passando como parâmetros os valores obtidos através dos getters da classe
                    $resBanco = $this->M_sala->inserir(
                        $this->getCodigo(),      // retorna o código da sala
                        $this->getDescricao(),   // retorna a descrição da sala
                        $this->getAndar(),       // retorna o andar da sala
                        $this->getCapacidade()   // retorna a capacidade da sala
                    );

                    // Verifica se a operação no banco foi realizada com sucesso
                    // código 1 significa sucesso, coloquei lá em cima
                    if ($resBanco['codigo'] == 1) {

                        // Define que a operação foi bem-sucedida
                        $sucesso = true;

                    } else {

                        // Caso ocorra erro no banco de dados
                        // o erro retornado pelo model é armazenado no array $erros
                        $erros[] = [
                            'codigo' => $resBanco['codigo'], // código do erro
                            'msg'    => $resBanco['msg']     // mensagem do erro
                        ];
                    } 
                }
            }
        } catch (Exception $e) {
                // Caso aconteça um erro inesperado no sistema ele será capturado aqui
                $erros[] = [
                    'codigo' => 0,
                    'msg' => 'Erro inesperado: ' . $e->getMessage()
                ];
            }
            
            // Se tudo deu certo, retorna sucesso e mensagem
            if ($sucesso == true) {             
                $retorno = [
                    'sucesso' => $sucesso,
                    'msg' => 'Sala cadastrada corretamente.'
                ];

            } else {

                // Caso contrário retorna sucesso falso e a lista de erros
                $retorno = [
                    'sucesso' => $sucesso,
                    'erros' => $erros
                ];
            }
           
            echo json_encode($retorno);  // Converte o array PHP em formato JSON permitindo que o frontend receba os dados nas variaveis privadas
    }
    
    
    // Declarando a função consultar 
    public function consultar() { 
    // Atributos para controlar o status do método
    $this->load->helper('geral_helper');
    $erros = []; // Array que vai armazenar possíveis erros
    $sucesso = false; // Variável de controle para indicar se deu tudo certo

        try { // Início do bloco try para capturar exceções

            $json = file_get_contents('php://input'); // Lê o corpo da requisição (JSON enviado pelo frontend)
            $resultado = json_decode($json); // Converte o JSON recebido em um objeto PHP

            $lista = [ // Define os campos esperados do frontend com valores padrão
                "codigo" => '0',
                "descricao" => '0',
                "andar" => '0',
                "capacidade" => '0'
            ];

            if (verificarParametros($resultado, $lista) != 1) { // Verifica se os parâmetros recebidos estão corretos
                // Validar vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.']; 
                // Adiciona erro caso os parâmetros estejam errados ou faltando
            } else {

                // Validar campos quanto ao tipo de dado e tamanho (Helper)
                $retornoCodigo = validarDadosConsulta($resultado->codigo, 'int'); 
                // Valida se "codigo" é um número inteiro válido
                $retornoDescricao = validarDadosConsulta($resultado->descricao, 'string'); 
                // Valida se "descricao" é uma string válida (não vazia)
                $retornoAndar = validarDadosConsulta($resultado->andar, 'int'); 
                // Valida se "andar" é um número inteiro válido
                $retornoCapacidade = validarDadosConsulta($resultado->capacidade, 'int'); 
                // Valida se "capacidade" é um número inteiro válido
            

                if ($retornoCodigo['codigoHelper'] != 0) { // Verifica se houve erro na validação do campo "codigo"
                    $erros[] = [ // Adiciona o erro no array de erros
                        'codigo' => $retornoCodigo['codigoHelper'], // Código do erro retornado pelo helper
                        'campo' => 'Codigo', // Nome do campo com erro
                        'msg' => $retornoCodigo['msg'] // Mensagem de erro
                    ];
                }

                if ($retornoDescricao['codigoHelper'] != 0) { // Verifica erro na validação de "descricao"
                    $erros[] = [
                        'codigo' => $retornoDescricao['codigoHelper'], // Código do erro
                        'campo' => 'Descrição', // Nome do campo
                        'msg' => $retornoDescricao['msg'] // Mensagem do erro
                    ];
                }

                if ($retornoAndar['codigoHelper'] != 0) { // Verifica erro na validação de "andar"
                    $erros[] = [
                        'codigo' => $retornoAndar['codigoHelper'], // Código do erro
                        'campo' => 'Andar', // Nome do campo
                        'msg' => $retornoAndar['msg'] // Mensagem do erro
                    ];
                }

                if ($retornoCapacidade['codigoHelper'] != 0) { // Verifica erro na validação de "capacidade"
                    $erros[] = [
                        'codigo' => $retornoCapacidade['codigoHelper'], // Código do erro
                        'campo' => 'Capacidade', // Nome do campo
                        'msg' => $retornoCapacidade['msg'] // Mensagem do erro
                    ];
                }

                // Se não encontrar erros
                if (empty($erros)) { // Verifica se o array de erros está vazio

                    $this->setCodigo($resultado->codigo); // Define o código no objeto atual
                    $this->setDescricao($resultado->descricao); // Define a descrição
                    $this->setAndar($resultado->andar); // Define o andar
                    $this->setCapacidade($resultado->capacidade); // Define a capacidade

                    $this->load->model("M_sala"); // Carrega o model responsável pelo banco

                    $resBanco = $this->M_sala->consultar( // Chama o método consultar no model
                        $this->getCodigo(), // Passa o código
                        $this->getDescricao(), // Passa a descrição
                        $this->getAndar(), // Passa o andar
                        $this->getCapacidade() // Passa a capacidade
                    );

                    if ($resBanco['codigo'] == 1) { // Verifica se a operação no banco foi bem-sucedida
                        $sucesso = true; // Marca como sucesso
                    } else {
                        // Captura erro do banco
                        $erros[] = [
                            'codigo' => $resBanco['codigo'], // Código de erro retornado pelo banco
                            'msg' => $resBanco['msg'] // Mensagem de erro
                        ];
                    }
                }   
            }  
        } catch (Exception $e) { 
            $erros[] = [
                'codigo' => 0, // Código genérico de erro
                'msg' => 'Erro inesperado: ' . $e->getMessage() // Mensagem da exceção
            ];
        }

            // Monta retorno único
            if ($sucesso == true) { // Se a operação foi bem-sucedida
                $retorno = [
                    'sucesso' => $sucesso, // true
                    'codigo' => $resBanco['codigo'], // Código retornado do banco
                    'msg' => $resBanco['msg'], // Mensagem do banco
                    'dados' => $resBanco['dados'] // Dados retornados da consulta
                ];
            } else {
                $retorno = [
                    'sucesso' => $sucesso, // false
                    'erros' => $erros // Lista de erros encontrados
                ];
        }

        // Transforma o array em JSON
        echo json_encode($retorno); // Retorna a resposta em formato JSON para o frontend
                
    }   

    public function alterar() {
    // Atributos para controlar o status de nosso método
    $erros = []; // Array que armazena os erros encontrados
    $sucesso = false; // Variável que indica se a operação foi bem-sucedida

    try { // Início do bloco try para capturar exceções

        $json = file_get_contents('php://input'); // Lê o corpo da requisição enviado pelo frontend em JSON
        $resultado = json_decode($json); // Converte o JSON recebido em um objeto PHP

        $lista = [ // Define os campos esperados vindos do frontend
            "codigo"     => '0',
            "descricao"  => '0',
            "andar"      => '0',
            "capacidade" => '0'
        ];

        if (verificarParametros($resultado, $lista) != 1) { // Verifica se os parâmetros recebidos estão corretos
            // Validar vindos de forma correta do frontend (Helper)
            $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.']; 
            // Adiciona erro caso os parâmetros estejam errados ou faltando
        } else {
                        // Pelo menos um dos três parâmetros precisam ter dados para acontecer a atualização
                if(trim($resultado->descricao) == '' && trim($resultado->andar) == '' &&
                    trim($resultado->capacidade) == '') {
                    $erros[] = ['codigo' => 12,
                                'msg' => 'Pelo menos um parâmetro precisa ser passado para atualização'];

                } else {
                    // Validar campos quanto ao tipo de dado e tamanho (Helper)
                    $retornoCodigo    = validarDados($resultado->codigo, 'int', true); // Valida código como inteiro obrigatório
                    $retornoDescricao = validarDadosConsulta($resultado->descricao, 'string'); // Valida descrição como string opcional
                    $retornoAndar     = validarDadosConsulta($resultado->andar, 'int'); // Valida andar como inteiro opcional
                    $retornoCapacidade = validarDadosConsulta($resultado->capacidade, 'int'); // Valida capacidade como inteiro opcional

                    if($retornoCodigo['codigoHelper'] != 0) { // Verifica erro na validação do código
                        $erros[] = ['codigo' => $retornoCodigo['codigoHelper'],
                                    'campo'  => 'Codigo',
                                    'msg'    => $retornoCodigo['msg']];
                    }

                    if($retornoDescricao['codigoHelper'] != 0) { // Verifica erro na validação da descrição
                        $erros[] = ['codigo' => $retornoDescricao['codigoHelper'],
                                    'campo'  => 'Descrição',
                                    'msg'    => $retornoDescricao['msg']];
                    }

                    if($retornoAndar['codigoHelper'] != 0) { // Verifica erro na validação do andar
                        $erros[] = ['codigo' => $retornoAndar['codigoHelper'],
                                    'campo'  => 'Andar',
                                    'msg'    => $retornoAndar['msg']];
                    }

                    if($retornoCapacidade['codigoHelper'] != 0) { // Verifica erro na validação da capacidade
                        $erros[] = ['codigo' => $retornoCapacidade['codigoHelper'],
                                    'campo'  => 'Capacidade',
                                    'msg'    => $retornoCapacidade['msg']];
                    } 

                    // Se não encontrar erros
                    if (empty($erros)) { // Verifica se o array de erros está vazio
                        $this->setCodigo($resultado->codigo);     // Define o código no objeto atual
                        $this->setDescricao($resultado->descricao); // Define a descrição
                        $this->setAndar($resultado->andar);         // Define o andar
                        $this->setCapacidade($resultado->capacidade); // Define a capacidade

                        $this->load->model('M_sala'); // Carrega o model responsável pelo banco

                        $resBanco = $this->M_sala->alterar( // Chama o método alterar no model
                            $this->getCodigo(),      // Passa o código
                            $this->getDescricao(),   // Passa a descrição
                            $this->getAndar(),       // Passa o andar
                            $this->getCapacidade()   // Passa a capacidade
                        );

                    if ($resBanco['codigo'] == 1) { // Verifica se a operação no banco foi bem-sucedida
                        $sucesso = true; // Marca como sucesso
                    } else {
                        // Captura erro do banco
                        $erros[] = [
                            'codigo' => $resBanco['codigo'], // Código de erro retornado pelo banco
                            'msg'    => $resBanco['msg']     // Mensagem de erro
                        ];
                    }
                } // fecha o empty($erros)
            }
        } 
    } catch (Exception $e) { // Captura qualquer exceção inesperada
                $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
    } // fecha o catch 
// Monta retorno único
    if ($sucesso == true) { // Se a operação foi bem-sucedida
        $retorno = [
            'sucesso' => $sucesso,          // true
            'codigo'  => $resBanco['codigo'], // Código retornado do banco
            'msg'     => $resBanco['msg']     // Mensagem do banco
        ];
    } else {
        $retorno = [
            'sucesso' => $sucesso, // false
            'erros'   => $erros    // Lista de erros encontrados
        ];
    }
    // Transforma o array em JSON
    echo json_encode($retorno); // Retorna a resposta em formato JSON para o frontend

    // fecha o método alterar()       
    }

    public function desativar() {
        // Atributos para controlar o status de nosso método
        $erros = []; // Array que armazena os erros encontrados
        $sucesso = false; // Variável que indica se a operação foi bem-sucedida

        try { // Início do bloco try para capturar exceções

            $json = file_get_contents('php://input'); // Lê o corpo da requisição enviado pelo frontend
            $resultado = json_decode($json); // Converte o JSON recebido em um objeto PHP

            $lista = [
                "codigo" => '0' // Define apenas o campo esperado do frontend
            ];

            if (verificarParam($resultado, $lista) != 1) { // Verifica se os parâmetros estão corretos
                // Validar vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {

                // Validar código quanto ao tipo de dado e tamanho (Helper)
                $retornoCodigo = validarDados($resultado->codigo, 'int', true); // Valida se o código é inteiro obrigatório

                if($retornoCodigo['codigoHelper'] != 0) { // Verifica se houve erro na validação do código
                    $erros[] = ['codigo' => $retornoCodigo['codigoHelper'],
                                'campo'  => 'Codigo',
                                'msg'    => $retornoCodigo['msg']];
                }

                // Se não encontrar erros
                if (empty($erros)) { // Verifica se o array de erros está vazio

                    $this->setCodigo($resultado->codigo); // Define o código no objeto atual

                    $this->load->model('M_sala'); // Carrega o model responsável pelo banco
                    $resBanco = $this->M_sala->desativar($this->getCodigo()); // Chama o método desativar passando apenas o código

                    if ($resBanco['codigo'] == 1) { // Verifica se a operação no banco foi bem-sucedida
                        $sucesso = true; // Marca como sucesso
                    } else {
                        // Captura erro do banco
                        $erros[] = [
                            'codigo' => $resBanco['codigo'], // Código de erro retornado pelo banco
                            'msg'    => $resBanco['msg']     // Mensagem de erro
                        ];
                    }
                }

            }
        } catch (Exception $e) { // Captura qualquer exceção inesperada
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        // Monta retorno único
        if ($sucesso == true) { // Se a operação foi bem-sucedida
            $retorno = [
                'sucesso' => $sucesso,           // true
                'codigo'  => $resBanco['codigo'], // Código retornado do banco
                'msg'     => $resBanco['msg']     // Mensagem do banco
            ];
        } else {
            $retorno = [
                'sucesso' => $sucesso, // false
                'erros'   => $erros    // Lista de erros encontrados
            ];
        }

        // Transforma o array em JSON
        echo json_encode($retorno); // Retorna a resposta em formato JSON para o frontend

    } // fechando a função desativar()

} // fecha a classe Sala
?>


