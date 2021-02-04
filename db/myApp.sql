CREATE TABLE users(

    id int NOT NULL AUTO_INCREMENT,
    email varchar (200) NOT NULL ,
    password text NOT NULL ,
    name varchar (500) NOT NULL ,
    school varchar (100) NOT NULL ,
    CONSTRAINT users_pk PRIMARY KEY (id)
);

/*
    Dummy record for users table
*/

INSERT INTO users (email, password, name, school) values ('avrpic8@gmail.com','1234','saeed','SmartElec');
