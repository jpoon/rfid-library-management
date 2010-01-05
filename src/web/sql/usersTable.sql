 #create database Library;

 drop table if exists Users;

 create table Users (
	FirstName varchar(25) not null,
	LastName varchar(25) not null,
	Password varchar(32) not null,
	UserName varchar(25) not null unique,
	UserType Enum('Student', 'Librarian', 'Admin') not null,
	Email varchar(25) not null unique,
	CardNo smallint not null auto_increment,
	#Fine decimal(6,2),
	primary key (CardNo)
 ) engine = myisam;
 
alter table Users auto_increment = 10000;