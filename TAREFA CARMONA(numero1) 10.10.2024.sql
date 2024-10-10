﻿
Create database Conta
use Conta;

CREATE TABLE contas_receber (
    CON_numero INT primary key,   
    CON_nome VARCHAR(40),
    CON_valor DECIMAL(10,2),
    CON_vencimento DATE,
    CON_banco VARCHAR(15)
);

insert into contas_receber (CON_nome, CON_numero, CON_valor, CON_vencimento, CON_banco) values
('ABC PAPELARIA', 100100, 5000.00, '2017-01-20', 'ITAU'),
('LIVRARIA FERNANDES', 100110, 2500.00, '2017-01-22', 'ITAU'),
('LIVRARIA FERNANDES', 100120, 1500.00, '2016-10-15', 'BRADESCO'),
('ABC PAPELARIA', 100130, 8000.00, '2016-10-15', 'SANTANDER'),
('LER E SABER', 200120, 10500.00, '2018-04-26', 'BANCO DO BRASIL'),
('LIVROS E CIA', 200125, 2000.00, '2018-04-26', 'BANCO DO BRASIL'),
('LER E SABER', 200130, 11000.00, '2018-09-26', 'ITAU'),
('PAPELARIA SILVA', 250350, 1500.00, '2018-01-26', 'BRADESCO'),
('LIVROS MM', 250360, 5000.00, '2018-04-26', 'ITAU'),
('LIVROS MM', 250370, 3400.00, '2018-04-26', 'SANTANDER'),
('PAPELARIA SILVA', 250380, 10500.00, '2018-04-26', 'BANCO DO BRASIL'),
('LIVROS E CIA', 453360, 4000.00, '2017-06-15', 'ITAU'),
('PAPEL E AFINS', 453365, 1500.00, '2017-05-15', 'BRADESCO'),
('PAPELARIA SILVA', 453370, 3500.00, '2017-04-15', 'ITAU'),
('LIVROS E CIA', 453380, 800.00, '2017-11-15', 'BANCO DO BRASIL'),
('ABC PAPELARIA', 880120, 1000.00, '2016-09-11', 'SANTANDER'),
('ABC PAPELARIA', 880130, 5000.00, '2017-12-12', 'ITAU'),
('PAPEL E AFINS', 888120, 4000.00, '2016-03-10', 'SANTANDER'),
('LER E SABER', 888132, 2500.00, '2017-05-01', 'ITAU');

SELECT CON_nome, CON_vencimento, CON_valor FROM contas_receber;

SELECT CON_numero FROM contas_receber WHERE CON_banco='ITAU';

SELECT CON_numero, CON_vencimento, CON_valor, CON_nome 
FROM contas_receber 
WHERE YEAR(CON_vencimento)=2017;

SELECT CON_numero, CON_vencimento, CON_valor, CON_nome
FROM contas_receber
WHERE CON_banco NOT IN('SANTANDER','ITAU');


SET SESSION group_concat_max_len = 1000000;

SELECT SUM(CON_valor) AS total_divida, GROUP_CONCAT(CON_numero) AS duplicatas
FROM contas_receber 
WHERE CON_nome = 'PAPELARIA SILVA';

DELETE FROM contas_receber 
WHERE CON_numero = 770710 AND CON_nome = 'LIVRARIA FERNANDES';

SELECT * 
FROM contas_receber 
ORDER BY CON_nome ASC;

SELECT CON_nome, CON_banco, CON_valor, CON_vencimento 
FROM contas_receber 
ORDER BY CON_vencimento ASC;

UPDATE contas_receber
SET CON_banco='SANTANDER'
WHERE CON_banco='BANCO DO BRASIL';

SELECT DISTINCT CON_nome 
FROM contas_receber 
WHERE CON_banco = 'BRADESCO';

SELECT SUM(CON_valor) AS total_recebimento 
FROM contas_receber 
WHERE CON_vencimento BETWEEN '2016-01-01' AND '2016-12-31';

SELECT SUM(CON_valor) AS total_recebimento 
FROM contas_receber 
WHERE CON_vencimento BETWEEN '2016-08-01' AND '2016-08-30';

SELECT * 
FROM contas_receber 
WHERE CON_nome = 'ABC PAPELARIA';

UPDATE contas_receber 
SET CON_valor = CON_valor * 1.15
WHERE CON_vencimento BETWEEN '2016-01-01' AND '2016-12-31';

UPDATE contas_receber 
SET CON_valor = CON_valor * 1.05 
WHERE CON_vencimento BETWEEN '2017-01-01' AND '2017-05-31' 
  AND CON_nome = 'LER E SABER';

SELECT AVG(CON_valor) AS media_valor 
FROM contas_receber 
WHERE YEAR(CON_vencimento) = 2016;

SELECT CON_numero, CON_nome 
FROM contas_receber 
WHERE CON_valor > 10000.00;

SELECT SUM(CON_valor) AS total_valor 
FROM contas_receber 
WHERE CON_banco = 'SANTANDER';

SELECT DISTINCT CON_nome 
FROM contas_receber 
WHERE CON_banco IN ('BRADESCO', 'ITAU');





