<?php
require 'db_manager.php';
class Concerto
{
    private $id;
    private $codice;
    private $titolo;
    private $descrizione;
    private $data_concerto;
    public function __construct($codice, $titolo, $descrizione, $data_concerto)
    {
        $this->__setCodice($codice);
        $this->__setTitolo($titolo);
        $this->__setDescrizione($descrizione);
        $this->__setDataConcerto($data_concerto);
    }
    private function __setId($var) //metodo per il setting dell'id del concerto
    {
        $this->id = $var;
    }
    public function __getId() //metodo per il getting dell'id del concerto
    {
        return $this->id;
    }
    public function __setCodice($var) //metodo per il setting del codice identificativo del concerto
    {
        $this->codice = $var;
    }
    public function __getCodice() //metodo per il getting del codice identificativo del concerto
    {
        return $this->codice;
    }
    public function __setTitolo($var) //metodo per il setting del titolo del concerto
    {
        $this->titolo = $var;
    }
    public function __getTitolo() //metodo per il getting del titolo del concerto
    {
        return $this->titolo;
    }
    public function __setDescrizione($var) //metodo per il setting della descrizione del concerto
    {
        $this->descrizione = $var;
    }
    public function __getDescrizione() //metodo per il getting della descrizione del concerto
    {
        return $this->descrizione;
    }
    public function __setDataConcerto($var) //metodo per il setting della data del concerto
    {
        $dateTimeObj = $var;
        if ($dateTimeObj == null) {
            $dateTimeObj = new DateTime();
        }
        $this->data_concerto = $dateTimeObj;
    }
    public function __getDataConcerto() //metodo per il getting della data del concerto
    {
        return $this->data_concerto;
    }
    public static function Create(array $params) //metodo per creare un record all'interno della tabella del database : ritorna l'id del record appena creato
    {
        $db = new dbManager('config.txt'); //classe utilizzata per gestire il database organizzazione_concerti
        $db->connessione(); //metodo per la connessione al database tramite PDO

        if ($db->insertInto($params)) {
            $id = $db->lastInsertId();
            $ritorno = Concerto::Find($id); //settaggio del ritorno : verrà impostato in modo da ritornare un oggetto concerto completamente configurato (avviene il set dell'id)
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
        if ($fetch = $db->Find($id)) { //metodo per la ricerca dell'oggetto tramite id all'interno della tabella concerti
            $new = new Concerto(strval($fetch->codice), strval($fetch->titolo), strval($fetch->descrizione), strval($fetch->data_concerto)); //creazione di un oggetto tramite gli attributi dell'ultimo oggetto "raccolto", fetchato
            $new->__setId($fetch->id); //settaggio dell'id 
            $db->close(); //chiusura connessione
            return $new; //ritorno dell'oggetto trovato
        }
        $db->close(); //chiusura connessione
        return false;
    }
    public static function FindAll()
    {
        $db = new dbManager('config.txt'); // Creazione di un'istanza di dbManager con il file di configurazione
        $db->connessione(); // Connessione al database tramite PDO
        if (!$concerti = $db->findAll()) { // Ottenimento di tutti i record dalla tabella dei concerti
            $db->close();
            return []; //ritorna un vettore vuoto in caso di errore
        }
        $db->close(); // Chiusura della connessione al database, indipendentemente dall'esito precedente
        return $concerti; // Restituzione dell'array dei concerti se presente, altrimenti un array vuoto
    }
    public function Delete() //metodo per l'eliminazione di un record dalla tabella concerti
    {
        $db = new dbManager("config.txt"); //classe utilizzata per gestire il database organizzazione_concerti
        $db->connessione(); //metodo per la connessione al database tramite PDO
        $concerto = Concerto::Find($this->__getId()); //metodo per la ricerca dell'oggetto tramite id all'interno della tabella concerti
        $id = $concerto->__getId();
        if ($result = $db->delete($id)) {
            $db->close(); //chiusura connessione
            return $result;
        }
        $db->close();
        return false;
    }
    public function Update(array $params) //metodo utilizzato per modificare un record con una nuova configurazione presente in $params
    {
        $new = $this->SetNew($params); //settaggio del nuovo record
        $db = new dbManager("config.txt"); //classe utilizzata per gestire il database organizzazione_concerti
        $db->connessione(); //metodo per la connessione al database tramite PDO

        $to_update = [
            //array chiave-valore utilizzato per la selezione e quindi modifica del record
            'codice' => $this->__getCodice(),
            'titolo' => $this->__getTitolo(),
            'descrizione' => $this->__getDescrizione(),
            'data_concerto' => $this->__getDataConcerto()
        ];
        $updated = [
            //array chiave-valore utilizzato per la selezione e quindi ricerca dell'id
            'codice' => $new->__getCodice(),
            'titolo' => $new->__getTitolo(),
            'descrizione' => $new->__getDescrizione(),
            'data_concerto' => $new->__getDataConcerto()
        ];
        if ($db->update($to_update, $updated)) { //metodo che all'interno del database sovrascriverà il nuovo record
            $this->__setCodice($updated['codice']);
            $this->__setTitolo($updated['titolo']);
            $this->__setDescrizione($updated['descrizione']);
            $this->__setDataConcerto($updated['data_concerto']);
            $db->close(); //chiusura connessione
            return true;
        }
        $db->close(); //chiusura connessione
        return false;
    }

    public function Sala()
{
    $db = new dbManager('config.txt');
    $db->connessione();

    $salaId = $this->__getId();
    $sala = Sala::Find($salaId);

    $db->close();

    if ($sala) {
        return new Sala(strval($sala->codice), strval($sala->nome), strval($sala->capienza));
    } else {
        return null;
    }
}

    private function SetNew(array $params) //metodo utilizzato per il settaggio di un nuovo record
    {
        $updated = Concerto::Find($this->__getId()); //metodo per la ricerca dell'oggetto tramite id all'interno della tabella concerti
        if (!empty($params['codice'])) {
            $updated->__setCodice($params['codice']);
        }
        if (!empty($params['titolo'])) {
            $updated->__setTitolo($params['titolo']);
        }
        if (!empty($params['descrizione'])) {
            $updated->__setDescrizione($params['descrizione']);
        }
        if (!empty($params['data_concerto'])) {
            $updated->__setDataConcerto($params['data_concerto']);
        }
        return $updated;
    }
    public function Show() //metodo per mostrare all'utente un record della tabella concerti : verrà tornata una stringa impostata in base agli attributi
    {
        $show = Concerto::Find($this->__getId()); //metodo per la ricerca dell'oggetto tramite id all'interno della tabella concerti
        return "ID : {$show->__getId()} - CODICE : {$show->__getCodice()} - TITOLO : {$show->__getDescrizione()} - DESCRIZIONE : {$show->__getDescrizione()} - DATA CONCERTO : {$show->__getDataConcerto()}\n";
    }
}
function create() //metodo utilizzata per l'implementazione del metodo create in un menu a riga di comando
{
    echo "Inserisci codice  : ";
    while (empty($codice)) { //controllo di validià della stringa in input, verrà richiesta finché non è ritenuta valida
        $codice = readline();
    }
    echo "Inserisci titolo : ";
    while (empty($titolo)) { //controllo di validià della stringa in input, verrà richiesta finché non è ritenuta valida
        $titolo = readline();
    }
    echo "Inserisci descrizione : ";
    while (empty($descrizione)) { //controllo di validià della stringa in input, verrà richiesta finché non è ritenuta valida
        $descrizione = readline();
    }
    echo "Inserisci data : ";
    while (empty($data)) { //controllo di validià della stringa in input, verrà richiesta finché non è ritenuta valida
        $data = readline();
        $dateTimeObj = DateTime::createFromFormat("Y-m-d", $data);
        if ($dateTimeObj == null) {
            $dateTimeObj = new DateTime();
        }
    }
    $params = [
        //creazione di una nuova configurazione da inserire
        'codice' => $codice,
        'titolo' => $titolo,
        'descrizione' => $descrizione,
        'data_concerto' => $dateTimeObj
    ];
    if (Concerto::Create($params)) { //verrà quindi creato un record nella tabella concerti grazie alla configurazione inserita dall'utente
        echo "Record creato.\n"; //se il record viene effettivamente inserito, viene comunicato all'utente
        return;
    }
    echo "Record non creato.\n";
}
function show() //metodo utilizzato per l'implementazione del metodo show su menu a riga di comando
{
    echo "inserisci id : ";
    $id = readline(); //richiesta in input dell'id del record che si vuole mostrare
    if ($concerto = Concerto::Find($id)) {
        echo $concerto->Show(); //se il record rispettivo viene trovato, questo viene mostrato
        return;
    }
    echo "ID non esistente.\n";
}
function update() //metodo utilizzata per l'implementazione del metodo update su menu a riga di comando
{
    echo "inserisci id del record da modificare : ";
    $id = readline(); //richiesta in input dell'id del record che si vuole modificare
    if ($concerto = Concerto::Find($id)) { //se il record rispettivo viene trovato, inizia il processo di update
        echo "Inserisci nuovo codice  : ";
        $codice = readline();
        if (empty($codice)) { //controllo di validià della stringa in input, se non valida verrà utilizzato l'attributo originale
            $codice = $concerto->__getCodice();
        }
        echo "Inserisci titolo : ";
        $titolo = readline();
        if (empty($titolo)) { //controllo di validià della stringa in input, se non valida verrà utilizzato l'attributo originale
            $titolo = $concerto->__getTitolo();
        }
        echo "Inserisci descrizione : ";
        $descrizione = readline();
        if (empty($descrizione)) { //controllo di validià della stringa in input, se non valida verrà utilizzato l'attributo originale
            $descrizione = $concerto->__getDescrizione();
        }
        echo "Inserisci data : ";
        $data = readline();
        $dateTimeObj = DateTime::createFromFormat('Y-m-d', $data);
        if ($dateTimeObj == null) { //controllo di validià della stringa in input, se non valida verrà utilizzato l'attributo originale
            $dateTimeObj = $concerto->__getDataConcerto();
        }

        $params = [
            //creazione di una nuova configurazione da utilizzare
            'codice' => $codice,
            'titolo' => $titolo,
            'descrizione' => $descrizione,
            'data_concerto' => $dateTimeObj
        ];
        if ($concerto->Update($params)) { //verrà quindi sovrascritto il record stesso utilizzando la nuova configurazione richiesta su linea di comando
            echo "Record modificato.\n"; //se il record viene effettivamente modificato, viene comunicato all'utente
            return;
        }
        echo "Record non modificabile.\n";
        return;
    }
    echo "ID non esistente.\n";
}
function delete() //metodo per l'implementazione della metodo d'istanza Delete() della classe Concerto su menu a riga di comando
{
    echo 'inserisci id : ';
    $id = readline(); //richiesta in input dell'id del record che si vuole eliminare
    if ($concerto = Concerto::Find($id)) { //se il record viene trovato, inizia il processo di delete
        if ($concerto->Delete()) { //se il record viene effettivamente eliminato, viene comunicato all'utente
            echo "record eliminato.\n";
            return;
        }
        echo "record non eliminabile.\n";
        return;
    }
    echo "ID non esistente.\n";
}
function find_all() //metodo utilizzata per l'implementazione della metodo Find_All() della classe Concerto su menu a riga di comando
{
    $concerti = Concerto::FindAll();
    if (count($concerti) == 0) { //viene avvisato l'utente nel caso l'array ritornato sia vuoto
        echo "database non abitato.\n";
    }
    foreach ($concerti as $a) { //verranno stampate a video le stringhe preimpostate in modo da visualizzare tutti i dati dei concerti inseriti nel database
        echo $a->Show();
    }
}

function sala()
{
    echo "Inserisci id del concerto per visualizzare la sala: ";
    $id = readline();
    if ($concerto = Concerto::Find($id)) {
        $sala = $concerto->sala();
        if ($sala) {
            echo "Sala associata al concerto:\n";
            echo "ID: " . $sala->__getId() . " - Codice: " . $sala->__getCodice() . " - Nome: " . $sala->__getNome() . " - Capienza: " . $sala->__getCapienza() . "\n";
        } else {
            echo "Nessuna sala associata al concerto.\n";
        }
        return;
    }
    echo "ID non esistente.\n";
}


while (1) { //menu a riga di comando
    echo "premere 1 per creare un record\n";
    echo "premere 2 per mostrare un record\n";
    echo "premere 3 per modificare un record\n";
    echo "premere 4 per eliminare un record\n";
    echo "premere 5 per mostrare tutti i records presenti nella tabella\n";
    echo "premere 6 per visualizzare la sala di un concerto\n";
    echo "premere 0 per terminare il programma\n";
    echo "scegli opzione : ";
    $option = readline();
    switch ($option) {
        case 0:
            exit('chiusura programma...');
        case 1:
            create();
            break;
        case 2:
            show();
            break;
        case 3:
            update();
            break;
        case 4:
            delete();
            break;
        case 5:
            find_all();
            break;
        case 6:
            sala();
            break;
    
    }
}
?>