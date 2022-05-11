create database crud;

use crud;

create table messages(
    id int not null primary key AUTO_INCREMENT,
    msg varchar(255) not null,
    uuid varchar(255) not null,
    time datetime DEFAULT current_timestamp
);
