<?php
'CREATE TABLE IF NOT EXISTS `mydb`.`users` (
  `idusers` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL,
  `role` VARCHAR(45) NULL,
  `password` VARCHAR(45) NULL,
  PRIMARY KEY (`idusers`),
  UNIQUE INDEX `idusers_UNIQUE` (`idusers` ASC))
ENGINE = InnoDB';