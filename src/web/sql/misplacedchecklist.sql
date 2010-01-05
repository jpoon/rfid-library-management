 drop table if exists MisPlacedCheckList;

 create table MisPlacedCheckList (
	ShelfID bigint not null,
	BookID bigint not null unique
 ) engine = myisam;
