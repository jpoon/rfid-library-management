drop table if exists misplaced;

create table misplaced (
    CallNo varchar(25) not null unique,
    shelf varchar(12) not null,
    foundShelf varchar(12) not null,
    primary key (CallNo),
    foreign key (CallNo) references books (CallNo)
) engine = myisam;

# trigger for modifying the state of the book in the Books table
create trigger misplacedEnter after insert on misplaced
for each row
update books
set state = "Missing"
where new.callno = callno;

create trigger misplacedExit before delete on misplaced
for each row
update books
set state = "In Stacks"
where old.callno = callno;