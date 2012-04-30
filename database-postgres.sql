-- Table: employee

-- DROP TABLE employee;

CREATE TABLE employee
(
  id serial NOT NULL, -- Employee code
  "name" character varying(80) NOT NULL, -- Employee name
  age integer, -- Employee age
  business_id integer NOT NULL, -- Business
  CONSTRAINT pk_employee_id PRIMARY KEY (id),
  CONSTRAINT fk_employee_bussiness FOREIGN KEY (business_id)
      REFERENCES business (id) MATCH SIMPLE
      ON UPDATE RESTRICT ON DELETE RESTRICT
)
WITH (
  OIDS=FALSE
);
ALTER TABLE employee OWNER TO postgres;
COMMENT ON COLUMN employee.id IS 'Employee code';
COMMENT ON COLUMN employee."name" IS 'Employee name';
COMMENT ON COLUMN employee.age IS 'Employee age';
COMMENT ON COLUMN employee.business_id IS 'Business';


-- Table: business

-- DROP TABLE business;

CREATE TABLE business
(
  id serial NOT NULL, -- Company code
  "name" character varying(100) NOT NULL, -- Company name
  CONSTRAINT pk_bussiness_id PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE business OWNER TO postgres;
COMMENT ON COLUMN business.id IS 'Company code';
COMMENT ON COLUMN business."name" IS 'Company name';


