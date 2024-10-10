create database vendas

use vendas

create table Produto(
Pro_id int primary key,
Pro_descriçao varchar(255),
Pro_preco float 
);

create table nf(
nf_numero int primary key,
nf_valor float,
nf_data date
);

create table itens (
    Produto_Codigo_Produto INTEGER,
    Nota_fiscal_Numero_NF INTEGER,
    Num_Item INTEGER,
    Qtde_Item INTEGER,
    PRIMARY KEY (Produto_Codigo_Produto, Nota_fiscal_Numero_NF, Num_Item),
    FOREIGN KEY (Produto_Codigo_Produto) REFERENCES Produto(Pro_id),
    FOREIGN KEY (Nota_fiscal_Numero_NF) REFERENCES nf(nf_numero)
);
alter table Produto Modify Pro_descriçao varchar(50); 

alter table nf add nf_icms float after nf_numero;

alter table Produto add Pro_peso float;


DESCRIBE Produto;

DESCRIBE nf;

ALTER TABLE nf CHANGE nf_valor nf_valortotal FLOAT;

ALTER TABLE nf DROP COLUMN nf_data;

DROP TABLE Itens;

ALTER TABLE nf 
RENAME TO Venda; 

INSERT INTO Venda (nf_numero, nf_valortotal, nf_icms)
VALUES (1, 1000.50, 18.00);





