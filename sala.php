<?php
require 'db_manager.php';
class Sala
{
    private $id;
    private $codice;
    private $nome;
    private $capienza;
    public function __construct($codice, $nome, $capienza)
    {
        $this->__setCodice($codice);
        $this->__setNome($nome);
        $this->__setCapienza($capienza);
    }
    private function __setId($var) //metodo per il setting dell'id della sala
    {
        $this->id = $var;
    }
    public function __getId() //metodo per il getting dell'id della sala
    {
        return $this->id;
    }
    public function __setCodice($var) //metodo per il setting del codice identificativo della sala
    {
        $this->codice = $var;
    }
    public function __getCodice() //metodo per il getting del codice identificativo della sala
    {
        return $this->codice;
    }
    public function __setNome($var) //metodo per il setting del nome della sala
    {
        $this->nome = $var;
    }
    public function __getNome() //metodo per il getting del nome della sala
    {
        return $this->nome;
    }
    public function __setCapienza($var) //metodo per il setting della capienza della sala
    {
        $this->capienza = $var;
    }
    public function __getCapienza() //metodo per il getting della capienza della sala
    {
        return $this->capienza;
    }

    public static function Create(array $params) //metodo per creare un record all'interno della tabella del database : ritorna l'id del record appena creato
    {
        $db = new dbManager('config.txt'); //classe utilizzata per gestire il database organizzazione_concerti
        $db->connessione(); //metodo per la connessione al database tramite PDO

        if ($db->insertInto($params)) {
            $id = $db->lastInsertId();
            $ritorno = Sala::Find($id); //settaggio del ritorno : verrà impostato in modo da ritornare un oggetto sala completamente configurato (avviene il set dell'id)
            $db->close(); //chiusura connessione
            return $ritorno;
        }
        $db->close(); //chiusura connessione
        return false;
    }
    public static function Find($id) //metodo per la dircerca di un record tramite il suo id : verrà ritornato il corrispondente record
    {
        $db = new dbManager("config.txt"); //classe utilizzata per gestire il database organizzazione_concerti
        $db->connessione(); //metodo per la connessione al database tramite PDO
        if ($fetch = $db->findSala($id)) { //metodo per la ricerca dell'oggetto tramite id all'interno della tabella sala
            $new = new Sala(strval($fetch->codice), strval($fetch->nome), strval($fetch->capienza)); //creazione di un oggetto tramite gli attributi dell'ultimo oggetto "raccolto", fetchato
            $new->__setId($fetch->id); //settaggio dell'id 
            $db->close(); //chiusura connessione
            return $new; //ritorno dell'oggetto trovato
        }
        $db->close(); //chiusura connessione
        return false;
    }
}
?>