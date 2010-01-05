
drop table if exists Books;

create table Books (
    CallNo varchar(25) not null unique,
    rfidNo bigint not null unique,
    Title varchar(50) not null,
    Author varchar(25) not null,
    Publisher varchar(25) not null,
    ISBN varchar(25) not null unique,
    State Enum('On Hold', 'On Loan', 'Returned', 'Missing', 'Damaged', 'In Stacks', 'In Reserve') not null,
    shelf varchar(12),
    primary key (CallNo)
) engine = myisam;

alter table Books add fulltext( CallNo, Title, Author, Publisher, ISBN );