<?php
class dbManager //classe utilizzata per gestire il database organizzazione_concerti
{
    private $host;
    private $dbname;
    private $user;
    private $password;
    private $connessione;
    private $stmt;
    public function __construct($filepath) //metodo costruttore della classe dbManager : verrà impostata la connessione al database in base al file di configurazione
    {
        $files = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->host = $files[0];
        $this->dbname = $files[1];
        $this->user = $files[2];
        $this->password = $files[3];
    }
    public function connessione() //metodo per la connessione al database tramite PDO
    {
        try {
            $this->connessione = new PDO("mysql:dbname={$this->dbname};host={$this->host}", $this->user, $this->password); //connessione al database
        } catch (PDOException $e) {
            die("connesione fallita : " . $e->getMessage()); //se viene catturata un eccezione tramite try catch, quindi trovato un errore durante la connessione al database, questo viene comunicato all'utente
        }
    }
    public function prepare($query) //metodo utilizzato per preparare il database alla query da eseguire
    {
        $this->stmt = $this->connessione->prepare($query);
    }
    public function bindParamS(string $token,string $value)//metodo utilizzato per fare il binding di STRINGHE ai parametri della query
    {
        $this->stmt->bindParam($token, $value, PDO::PARAM_STR);
    }
    public function bindParamI(string $token,int $value)//metodo utilizzato per fare il binding di INTERI ai parametri della query
    {
        $this->stmt->bindParam($token,$value,PDO::PARAM_INT);        
    }
    public function execute() //metodo utilizzato per controllare l'esito della query eseguita
    {
        return $this->stmt->execute();
    }
    public function close() //metodo utilizzato per chiudere la connessione al database
    {
        $this->connessione = null;
    }
    public function insertInto(array $params) //metodo utilizzato per la query di inserimento di record dentro la tabella concerto
    {
        $str_data = $params['data_concerto']->format("Y-m-d"); //conversione in stringa della data del concerto
        if($this->checkCode($params['codice'])>0) {//controllo dell'univocità del codice identificativo che si vuole inserire
            $this->close();
            return false;
        }
        $this->close();//chiusura della connessione in seguito ad aver controllato il codice (viene inizialmente aperta in Classe Concerto)

        $this->connessione();//connessione al database
        $this->prepare("insert into organizzazione_concerti.concerti(codice,titolo,descrizione,data_concerto) values (:codice,:titolo,:descrizione,:data_concerto)"); //metodo per la preparazione della query da eseguire
        $this->bindParamS(':codice',$params['codice']);//binding del parametro desiderato
        $this->bindParamS(':titolo',$params['titolo']);//binding del parametro desiderato
        $this->bindParamS(':descrizione',$params['descrizione']);//binding del parametro desiderato
        $this->bindParamS(':data_concerto',$str_data);//binding del parametro desiderato

        return $this->Execute(); //viene ritornato l'esito dell'esecuzione della query eseguita
    }
    private function checkCode($codice)//metodo utilizzato all'interno delle query di creazione e di modifica per controllare l'univocità del codice identificativo di un record
    {
        $this->prepare("select count(*) as conta from organizzazione_concerti.concerti where codice = :codice");//preparazione della query da eseguire
        $this->bindParamS(':codice',$codice);//binding del parametro desiderato
        if (!$this->Execute()) { //viene controllato l'esito della query eseguita
            return false;
        }
        return $this->fetchNext()->conta;//viene ritornato il valore all'interno della colonna creata a nome 'conta'
    }
    public function lastInsertId() //metodo utilizzato per trovare l'id dell'ultimo record inserito all'interno del database
    {
        return $this->connessione->lastInsertId();
    }
    public function find($id) //metodo utilizzato per la query select: in questo metodo verra ritornato il primo record positivo alla query da eseguire
    {
        $this->prepare('select * from organizzazione_concerti.concerti where id = :id'); //preparazione della query da eseguire
        $this->bindParamI(':id',$id);//binding del parametro desiderato
        if (!$this->execute()) { //viene controllato l'esito della query eseguita
            return false;
        }
        return $this->fetchNext(); //ritorno del primo elemento fetchato che rispondeva alla query eseguita
    }
    public function fetchNext() //metodo utilizzato per eseguire fetch dei record da manipolare
    {
        return $this->stmt->fetch(PDO::FETCH_OBJ); //tramite il flag PDO::FETCH_OBJ vengono creati oggetti con proprietà nominate come le colonne della tabella fetchata
    }
    public function findAll() //metodo utilizzato per la query di select : in questo metodo verranno trovati tutti i record presenti all'interno della tabella scelta
    {
        $i = 0; //indice
        $concerti = []; //vettore che conterrà (o no) tutti i record inseriti all'intero del database
        $this->prepare('select * from organizzazione_concerti.concerti'); //preparazione della query da eseguire
        if (!$this->execute()) { //viene controllato l'esito della query eseguita
            return false;
        }

        while ($record = $this->fetchNext()) { //ritorno ciclico del primo elemento fetchato che rispondeva alla query eseguita, fino alla fine della tabella
            $tmp = concerto::Find($record->id); //viene configurato completamente il record fetchato (viene settato l'id)
            $concerti[$i++] = $tmp;
        }
        return $concerti; //ritorno dell'array contenente i record della tabella del database
    }
    public function delete(int $id) //metodo utilizzato per la query delete : in questo modo verranno eliminati i record positivi alla query da eseguire
    {
        $this->prepare("delete from organizzazione_concerti.concerti where id = :id"); //preparazione della query da eseguire
        $this->bindParamI(':id',$id);//binding del parametro desiderato

        return $this->execute(); //ritorno del primo elemento fetchato che rispondeva alla query eseguita
    }
    public function update(array $to_update, array $updated) //metodo utilizzato per la query update : in questo modo verranno modificati record positivi alla query da eseguire
    {
        $str_data1 = $to_update['data_concerto'];
        $data2 = $updated['data_concerto'];
        if (is_string($data2)) { //si verifica che il value presente sia una stringa, e quindi non modificata in precedenza
            $str_data2 = $str_data1;
        } else {
            $str_data2 = $data2->format('Y-m-d');
        }
        if(strcmp($to_update['codice'],$updated['codice'])!=0 && $this->checkCode($updated['codice'])>0) {//controllo dell'univocità del codice identificativo che si vuole inserire
            $this->close();//chiusura della connessione
            return false;//ritorno che conferma l'invalidità del codice univoco
        }
        $this->close();//chiusura della connessione in seguito ad aver controllato il codice (viene inizialmente aperta in Classe Concerto)

        $this->connessione(); //connessione al database
        $this->prepare('update organizzazione_concerti.concerti set codice = :codice, titolo = :titolo, descrizione = :descrizione, data_concerto = :data_concerto
        where codice = :codice2 and titolo = :titolo2 and descrizione = :descrizione2 and data_concerto = :data_concerto2'); //preparazione della query da eseguire
        $this->bindParamS(':codice',$updated['codice']);//binding del parametro desiderato
        $this->bindParamS(':titolo',$updated['titolo']);//binding del parametro desiderato
        $this->bindParamS(':descrizione',$updated['descrizione']);//binding del parametro desiderato
        $this->bindParamS(':data_concerto',$str_data2);//binding del parametro desiderato
        
        $this->bindParamS(':codice2',$to_update['codice']);//binding del parametro desiderato
        $this->bindParamS(':titolo2',$to_update['titolo']);//binding del parametro desiderato
        $this->bindParamS(':descrizione2',$to_update['descrizione']);//binding del parametro desiderato
        $this->bindParamS(':data_concerto2',$str_data1);//binding del parametro desiderato

        return $this->execute();
    }

    public function findSala($id) // method to find a Sala record by its ID
    {
        $this->prepare('select * from organizzazione_concerti.sale where id = :id');
        $this->bindParamI(':id', $id);
        if (!$this->execute()) {
            return false;
        }
        return $this->fetchNext();
    }

    
}
?>