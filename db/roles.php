<?php
'CREATE TABLE IF NOT EXISTS `mydb`.`roles` (
`idroles` INT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL,
  `idusers` INT NULL,
  PRIMARY KEY (`idroles`),
  INDEX `role_user_idx` (`idusers` ASC),
  CONSTRAINT `role_user`
    FOREIGN KEY (`idusers`)
    REFERENCES `mydb`.`users` (`idusers`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB';