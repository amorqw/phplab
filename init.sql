CREATE TABLE IF NOT EXISTS questions (
id SERIAL PRIMARY KEY,
text TEXT NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS responses (
    id SERIAL PRIMARY KEY,
    questions_id INTEGER REFERENCES questions(id),
    session_id VARCHAR(255),
    answer_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

INSERT INTO questions (text)
VALUES ('Ваш любимый язык программирования?');

INSERT INTO questions (text)
VALUES ('Опишите текущую погоду');