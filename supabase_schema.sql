-- SmartBrew Cafè schema for Supabase (Postgres)
-- Run once in the Supabase SQL Editor before using the app.

CREATE TABLE customer_login (
    customer_id     BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    username        VARCHAR(100) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,
    address         TEXT,
    firstname       VARCHAR(100),
    lastname        VARCHAR(100),
    contactnumber   VARCHAR(30),
    unique_qr       VARCHAR(100) NOT NULL UNIQUE,
    stamps          INT NOT NULL DEFAULT 0
);

CREATE TABLE admin_login (
    admin_id        BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    username        VARCHAR(100) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL
);

CREATE TABLE customer_cart (
    cart_id         BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    customer_id     BIGINT NOT NULL REFERENCES customer_login(customer_id) ON DELETE CASCADE,
    item_id         INT NOT NULL,
    name            VARCHAR(150) NOT NULL,
    quantity        INT NOT NULL DEFAULT 1,
    price           NUMERIC(10, 2) NOT NULL,
    UNIQUE (customer_id, item_id)
);

CREATE TABLE transactions (
    transaction_id  BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    customer_id     BIGINT REFERENCES customer_login(customer_id) ON DELETE SET NULL,
    first_name      VARCHAR(100),
    last_name       VARCHAR(100),
    contact_number  VARCHAR(30),
    transaction_date TIMESTAMP NOT NULL DEFAULT NOW(),
    total_amount    NUMERIC(10, 2) NOT NULL,
    payment_method  VARCHAR(30) NOT NULL,
    status          VARCHAR(30) NOT NULL DEFAULT 'Completed',
    address         TEXT,
    account_number  VARCHAR(50),
    payment_receipt VARCHAR(255)
);

CREATE INDEX idx_customer_cart_customer_id ON customer_cart(customer_id);
CREATE INDEX idx_transactions_customer_id ON transactions(customer_id);
CREATE INDEX idx_transactions_transaction_date ON transactions(transaction_date);
