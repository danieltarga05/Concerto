create database if not exists organizzazione_concerti;

create table if not exists organizzazione_concerti.concerti
(
	id int not null auto_increment primary key,
	codice varchar(5),
	titolo varchar(50),
	descrizione varchar(100),
	data_concerto datetime
);