 drop table if exists shelves;

 create table shelves (
	ShelfID bigint not null unique,
	description varchar(12) not null unique
 ) engine = myisam;
