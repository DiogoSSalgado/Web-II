<?php
class Form
{
  private $message = "";
  private $error = "";
  public function __construct()
  {
    Transaction::open();
  }
  public function controller()
  {
    $form = new Template("restrict/view/form.html");
    $form->set("id", "");
    $form->set("filme", "");
    $form->set("sinopse", "");
    $form->set("elenco", "");
    $this->message = $form->saida();
  }
  public function salvar()
  {
    if (isset($_POST["filme"]) && isset($_POST["sinopse"]) && isset($_POST["elenco"])) {
      try {
        $conexao = Transaction::get();
        $locadora = new Crud("locadora");
        $filme = $conexao->quote($_POST["filme"]);
        $config = $conexao->quote($_POST["sinopse"]);
        $elenco = $conexao->quote($_POST["elenco"]);
        if (empty($_POST["id"])) {
          $locadora->insert(
            "filme, sinopse, elenco",
            "$filme, $config, $elenco"
          );
        } else {
          $id = $conexao->quote($_POST["id"]);
          $locadora->update(
            "filme = $filme, sinopse = $config, elenco = $elenco",
            "id = $id"
          );
        }
        $this->message = $locadora->getMessage();
        $this->error = $locadora->getError();
      } catch (Exception $e) {
        $this->message = $e->getMessage();
        $this->error = true;
      }
    } else {
      $this->message = "Campos não informados!";
      $this->error = true;
    }
  }
  public function editar()
  {
    if (isset($_GET["id"])) {
      try {
        $conexao = Transaction::get();
        $id = $conexao->quote($_GET["id"]);
        $locadora = new Crud("locadora");
        $resultado = $locadora->select("*", "id = $id");
        if (!$locadora->getError()) {
          $form = new Template("view/form.html");
          foreach ($resultado[0] as $cod => $elenco) {
            $form->set($cod, $elenco);
          }
          $this->message = $form->saida();
        } else {
          $this->message = $locadora->getMessage();
          $this->error = true;
        }
      } catch (Exception $e) {
        $this->message = $e->getMessage();
        $this->error = true;
      }
    }
  }
  public function getMessage()
  {
    if (is_string($this->error)) {
      return $this->message;
    } else {
      $msg = new Template("view/msg.html");
      if ($this->error) {
        $msg->set("cor", "danger");
      } else {
        $msg->set("cor", "success");
      }
      $msg->set("msg", $this->message);
      $msg->set("uri", "?class=Tabela");
      return $msg->saida();
    }
  }
  public function __destruct()
  {
    Transaction::close();
  }
}