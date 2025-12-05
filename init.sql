CREATE TABLE surveys
(
    id          UUID      DEFAULT uuid_generate_v4() PRIMARY KEY,
    first_name  VARCHAR(50) NOT NULL,
    last_name   VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    q1_answer   VARCHAR(50),
    q2_answer   VARCHAR(50),
    q3_answer   VARCHAR(50),
    q4_answer   VARCHAR(50),
    q5_answer   VARCHAR(50),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);