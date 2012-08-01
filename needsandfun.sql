SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

DROP SCHEMA IF EXISTS `needsandfun` ;
CREATE SCHEMA IF NOT EXISTS `needsandfun` DEFAULT CHARACTER SET utf8 ;
USE `needsandfun` ;

-- -----------------------------------------------------
-- Table `needsandfun`.`users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`users` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`users` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `login` VARCHAR(50) NOT NULL ,
  `password` VARCHAR(32) NOT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `blocked` TINYINT(1)  NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `needsandfun`.`tokens`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`tokens` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`tokens` (
  `user_id` INT NOT NULL ,
  `token` VARCHAR(32) NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`user_id`) ,
  CONSTRAINT `fk_tokens_users`
    FOREIGN KEY (`user_id` )
    REFERENCES `needsandfun`.`users` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `needsandfun`.`clients`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`clients` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`clients` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `user_id` INT NOT NULL ,
  `first_name` VARCHAR(100) NULL ,
  `last_name` VARCHAR(100) NULL ,
  `phone` VARCHAR(100) NULL ,
  `email` VARCHAR(45) NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_clients_users1`
    FOREIGN KEY (`user_id` )
    REFERENCES `needsandfun`.`users` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_clients_users1` ON `needsandfun`.`clients` (`user_id` ASC) ;


-- -----------------------------------------------------
-- Table `needsandfun`.`orders`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`orders` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`orders` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `client_id` INT NOT NULL ,
  `price` VARCHAR(45) NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_orders_clients1`
    FOREIGN KEY (`client_id` )
    REFERENCES `needsandfun`.`clients` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_orders_clients1` ON `needsandfun`.`orders` (`client_id` ASC) ;


-- -----------------------------------------------------
-- Table `needsandfun`.`statuses`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`statuses` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`statuses` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `needsandfun`.`order_statuses`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`order_statuses` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`order_statuses` (
  `order_id` INT NOT NULL ,
  `status_id` INT NOT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`order_id`, `status_id`) ,
  CONSTRAINT `fk_order_statuses_orders1`
    FOREIGN KEY (`order_id` )
    REFERENCES `needsandfun`.`orders` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_statuses_statuses1`
    FOREIGN KEY (`status_id` )
    REFERENCES `needsandfun`.`statuses` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_order_statuses_statuses1` ON `needsandfun`.`order_statuses` (`status_id` ASC) ;


-- -----------------------------------------------------
-- Table `needsandfun`.`brands`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`brands` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`brands` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(150) NOT NULL ,
  `picture` VARCHAR(45) NULL ,
  `description` TEXT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `deleted` TIMESTAMP NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `needsandfun`.`types`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`types` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`types` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NULL ,
  `description` TEXT NULL ,
  `created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `needsandfun`.`goods`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`goods` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`goods` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `brand_id` INT(11) NOT NULL ,
  `type_id` INT(11) NOT NULL ,
  `discount` INT(11) NOT NULL DEFAULT '0' ,
  `is_available` TINYINT(1) NOT NULL DEFAULT '0' ,
  `sex` TINYINT(1) NOT NULL ,
  `age` INT(30) NOT NULL ,
  `article` VARCHAR(100) NOT NULL ,
  `name` VARCHAR(100) NULL DEFAULT NULL ,
  `description` TEXT NULL DEFAULT NULL ,
  `compound` TEXT NULL DEFAULT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `deleted` TIMESTAMP NULL ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_goods_brands1`
    FOREIGN KEY (`brand_id` )
    REFERENCES `needsandfun`.`brands` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_goods_types1`
    FOREIGN KEY (`type_id` )
    REFERENCES `needsandfun`.`types` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_goods_types1` ON `needsandfun`.`goods` (`type_id` ASC) ;

CREATE INDEX `fk_goods_brands1` ON `needsandfun`.`goods` (`brand_id` ASC) ;


-- -----------------------------------------------------
-- Table `needsandfun`.`sizes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`sizes` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`sizes` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `good_id` INT NOT NULL ,
  `name` VARCHAR(100) NULL ,
  `price` INT NOT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `deleted` TIMESTAMP NULL ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_sizes_goods1`
    FOREIGN KEY (`good_id` )
    REFERENCES `needsandfun`.`goods` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_sizes_goods1` ON `needsandfun`.`sizes` (`good_id` ASC) ;


-- -----------------------------------------------------
-- Table `needsandfun`.`order_goods`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`order_goods` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`order_goods` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `order_id` INT NOT NULL ,
  `size_id` INT NOT NULL ,
  `price` INT NOT NULL DEFAULT '0' ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_order_goods_orders1`
    FOREIGN KEY (`order_id` )
    REFERENCES `needsandfun`.`orders` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_goods_sizes1`
    FOREIGN KEY (`size_id` )
    REFERENCES `needsandfun`.`sizes` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_order_goods_sizes1` ON `needsandfun`.`order_goods` (`size_id` ASC) ;

CREATE INDEX `fk_order_goods_orders1` ON `needsandfun`.`order_goods` (`order_id` ASC) ;


-- -----------------------------------------------------
-- Table `needsandfun`.`deliveries`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`deliveries` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`deliveries` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `order_id` INT NOT NULL ,
  `type` ENUM('courier', 'metro', 'pickup') NOT NULL DEFAULT 'courier' ,
  `price` INT NOT NULL DEFAULT 0 ,
  `address` VARCHAR(250) NULL ,
  `recall` VARCHAR(250) NULL ,
  `date` TIMESTAMP NULL DEFAULT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_deliveries_orders1`
    FOREIGN KEY (`order_id` )
    REFERENCES `needsandfun`.`orders` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_deliveries_orders1` ON `needsandfun`.`deliveries` (`order_id` ASC) ;


-- -----------------------------------------------------
-- Table `needsandfun`.`categories`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`categories` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`categories` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `parent_id` INT NULL ,
  `is_visible` TINYINT(1)  NOT NULL DEFAULT 0 ,
  `weight` INT NOT NULL DEFAULT 0 ,
  `name` VARCHAR(45) NOT NULL ,
  `description` TEXT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `deleted` TIMESTAMP NULL ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_categories_categories1`
    FOREIGN KEY (`parent_id` )
    REFERENCES `needsandfun`.`categories` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_categories_categories1` ON `needsandfun`.`categories` (`parent_id` ASC) ;


-- -----------------------------------------------------
-- Table `needsandfun`.`goods_categories`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`goods_categories` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`goods_categories` (
  `good_id` INT NOT NULL ,
  `category_id` INT NOT NULL ,
  `is_visible` TINYINT(1)  NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`good_id`, `category_id`) ,
  CONSTRAINT `fk_goods_categories_goods1`
    FOREIGN KEY (`good_id` )
    REFERENCES `needsandfun`.`goods` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_goods_categories_categories1`
    FOREIGN KEY (`category_id` )
    REFERENCES `needsandfun`.`categories` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_goods_categories_categories1` ON `needsandfun`.`goods_categories` (`category_id` ASC) ;


-- -----------------------------------------------------
-- Table `needsandfun`.`property_types`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`property_types` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`property_types` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `type` ENUM('string', 'text', 'number', 'array', 'range') NOT NULL DEFAULT 'string' ,
  `name` VARCHAR(45) NOT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `needsandfun`.`properties`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`properties` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`properties` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `property_type_id` INT NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_properties_property_types1`
    FOREIGN KEY (`property_type_id` )
    REFERENCES `needsandfun`.`property_types` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_properties_property_types1` ON `needsandfun`.`properties` (`property_type_id` ASC) ;


-- -----------------------------------------------------
-- Table `needsandfun`.`type_properties`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`type_properties` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`type_properties` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `type_id` INT NOT NULL ,
  `property_id` INT NOT NULL ,
  `weight` INT NOT NULL DEFAULT 0 ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_type_properties_properties1`
    FOREIGN KEY (`property_id` )
    REFERENCES `needsandfun`.`properties` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_type_properties_types1`
    FOREIGN KEY (`type_id` )
    REFERENCES `needsandfun`.`types` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 13
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_type_properties_properties1` ON `needsandfun`.`type_properties` (`property_id` ASC) ;

CREATE INDEX `fk_type_properties_types1` ON `needsandfun`.`type_properties` (`type_id` ASC) ;


-- -----------------------------------------------------
-- Table `needsandfun`.`property_values`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`property_values` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`property_values` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `property_id` INT NULL ,
  `value` VARCHAR(45) NULL ,
  `description` TEXT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_property_values_properties1`
    FOREIGN KEY (`property_id` )
    REFERENCES `needsandfun`.`properties` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_property_values_properties1` ON `needsandfun`.`property_values` (`property_id` ASC) ;


-- -----------------------------------------------------
-- Table `needsandfun`.`goods_properties`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`goods_properties` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`goods_properties` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `good_id` INT NOT NULL ,
  `property_id` INT NOT NULL ,
  `value` VARCHAR(100) NULL ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_goods_properties_goods1`
    FOREIGN KEY (`good_id` )
    REFERENCES `needsandfun`.`goods` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_goods_properties_properties1`
    FOREIGN KEY (`property_id` )
    REFERENCES `needsandfun`.`properties` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_goods_properties_goods1` ON `needsandfun`.`goods_properties` (`good_id` ASC) ;

CREATE INDEX `fk_goods_properties_properties1` ON `needsandfun`.`goods_properties` (`property_id` ASC) ;


-- -----------------------------------------------------
-- Table `needsandfun`.`pictures`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`pictures` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`pictures` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `good_id` INT NOT NULL ,
  `picture_id` INT NULL ,
  `weight` INT NOT NULL DEFAULT 0 ,
  `type` ENUM('full', 'medium', 'image', 'thumb', 'icon') NOT NULL DEFAULT 'full' ,
  `filename` VARCHAR(45) NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_pictures_goods1`
    FOREIGN KEY (`good_id` )
    REFERENCES `needsandfun`.`goods` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pictures_pictures1`
    FOREIGN KEY (`picture_id` )
    REFERENCES `needsandfun`.`pictures` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_pictures_goods1` ON `needsandfun`.`pictures` (`good_id` ASC) ;

CREATE INDEX `fk_pictures_pictures1` ON `needsandfun`.`pictures` (`picture_id` ASC) ;


-- -----------------------------------------------------
-- Table `needsandfun`.`page_types`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`page_types` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`page_types` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `needsandfun`.`pages`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`pages` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`pages` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `page_type_id` INT NOT NULL ,
  `name` VARCHAR(200) NOT NULL ,
  `contents` LONGTEXT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_pages_page_types1`
    FOREIGN KEY (`page_type_id` )
    REFERENCES `needsandfun`.`page_types` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_pages_page_types1` ON `needsandfun`.`pages` (`page_type_id` ASC) ;


-- -----------------------------------------------------
-- Table `needsandfun`.`metro_lines`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`metro_lines` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`metro_lines` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `color` VARCHAR(45) NOT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT current_timestamp ,
  `deleted` TIMESTAMP NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `needsandfun`.`metros`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`metros` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`metros` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `metroline_id` INT NOT NULL ,
  `name` VARCHAR(150) NOT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `deleted` TIMESTAMP NULL ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_metros_metro_lines1`
    FOREIGN KEY (`metroline_id` )
    REFERENCES `needsandfun`.`metro_lines` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX `fk_metros_metro_lines1` ON `needsandfun`.`metros` (`metroline_id` ASC) ;


-- -----------------------------------------------------
-- Table `needsandfun`.`place_categories`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`place_categories` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`place_categories` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `deleted` TIMESTAMP NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `needsandfun`.`places`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`places` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`places` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `category_id` INT NOT NULL ,
  `metro_id` INT NULL ,
  `name` VARCHAR(255) NOT NULL ,
  `address` VARCHAR(255) NOT NULL ,
  `description` TEXT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `deleted` TIMESTAMP NULL ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_places_metros1`
    FOREIGN KEY (`metro_id` )
    REFERENCES `needsandfun`.`metros` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_places_category1`
    FOREIGN KEY (`category_id` )
    REFERENCES `needsandfun`.`place_categories` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX `fk_places_metros1` ON `needsandfun`.`places` (`metro_id` ASC) ;

CREATE INDEX `fk_places_category1` ON `needsandfun`.`places` (`category_id` ASC) ;


-- -----------------------------------------------------
-- Table `needsandfun`.`events`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `needsandfun`.`events` ;

CREATE  TABLE IF NOT EXISTS `needsandfun`.`events` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `place_id` INT NOT NULL ,
  `name` VARCHAR(45) NOT NULL ,
  `description` TEXT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `deleted` TIMESTAMP NULL ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_events_places1`
    FOREIGN KEY (`place_id` )
    REFERENCES `needsandfun`.`places` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX `fk_events_places1` ON `needsandfun`.`events` (`place_id` ASC) ;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `needsandfun`.`statuses`
-- -----------------------------------------------------
START TRANSACTION;
USE `needsandfun`;
INSERT INTO `needsandfun`.`statuses` (`id`, `name`, `created`) VALUES (1, 'Новый', NULL);
INSERT INTO `needsandfun`.`statuses` (`id`, `name`, `created`) VALUES (2, 'В обработке', NULL);
INSERT INTO `needsandfun`.`statuses` (`id`, `name`, `created`) VALUES (3, 'Обработан', NULL);
INSERT INTO `needsandfun`.`statuses` (`id`, `name`, `created`) VALUES (4, 'Завершен', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `needsandfun`.`brands`
-- -----------------------------------------------------
START TRANSACTION;
USE `needsandfun`;
INSERT INTO `needsandfun`.`brands` (`id`, `name`, `picture`, `description`, `created`, `deleted`) VALUES (1, 'Бренд №1', NULL, NULL, NULL, NULL);
INSERT INTO `needsandfun`.`brands` (`id`, `name`, `picture`, `description`, `created`, `deleted`) VALUES (2, 'Бренд №2', NULL, NULL, NULL, NULL);
INSERT INTO `needsandfun`.`brands` (`id`, `name`, `picture`, `description`, `created`, `deleted`) VALUES (3, 'Бренд №3', NULL, NULL, NULL, NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `needsandfun`.`types`
-- -----------------------------------------------------
START TRANSACTION;
USE `needsandfun`;
INSERT INTO `needsandfun`.`types` (`id`, `name`, `description`, `created`) VALUES (1, 'Товар №1', NULL, NULL);
INSERT INTO `needsandfun`.`types` (`id`, `name`, `description`, `created`) VALUES (2, 'Товар №2', NULL, NULL);
INSERT INTO `needsandfun`.`types` (`id`, `name`, `description`, `created`) VALUES (3, 'Товар №3', NULL, NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `needsandfun`.`goods`
-- -----------------------------------------------------
START TRANSACTION;
USE `needsandfun`;
INSERT INTO `needsandfun`.`goods` (`id`, `brand_id`, `type_id`, `discount`, `is_available`, `sex`, `age`, `article`, `name`, `description`, `compound`, `created`, `deleted`) VALUES (1, 1, 1, 5, 1, 1, 61, '1000', 'Голубой худи с \rдвойным капюшоном от Moleskine', 'Нежный худи от Moleskine для самых модных детей. Идеально \rподходит для активных детей, не боящихся снега и грязи.', 'Полиэстер/хлопок 100%. \rРоссия-Китай', NULL, NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `needsandfun`.`sizes`
-- -----------------------------------------------------
START TRANSACTION;
USE `needsandfun`;
INSERT INTO `needsandfun`.`sizes` (`id`, `good_id`, `name`, `price`, `created`, `deleted`) VALUES (1, 1, 'S', 3000, NULL, '0');
INSERT INTO `needsandfun`.`sizes` (`id`, `good_id`, `name`, `price`, `created`, `deleted`) VALUES (2, 1, 'M', 3500, NULL, NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `needsandfun`.`categories`
-- -----------------------------------------------------
START TRANSACTION;
USE `needsandfun`;
INSERT INTO `needsandfun`.`categories` (`id`, `parent_id`, `is_visible`, `weight`, `name`, `description`, `created`, `deleted`) VALUES (1, NULL, 1, 0, 'Игрушки', NULL, NULL, NULL);
INSERT INTO `needsandfun`.`categories` (`id`, `parent_id`, `is_visible`, `weight`, `name`, `description`, `created`, `deleted`) VALUES (2, 1, 0, 1, 'Развивающие', NULL, NULL, NULL);
INSERT INTO `needsandfun`.`categories` (`id`, `parent_id`, `is_visible`, `weight`, `name`, `description`, `created`, `deleted`) VALUES (3, 1, 1, 2, 'Настольные', NULL, NULL, NULL);
INSERT INTO `needsandfun`.`categories` (`id`, `parent_id`, `is_visible`, `weight`, `name`, `description`, `created`, `deleted`) VALUES (4, 1, 0, 3, 'Мягкие', NULL, NULL, NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `needsandfun`.`goods_categories`
-- -----------------------------------------------------
START TRANSACTION;
USE `needsandfun`;
INSERT INTO `needsandfun`.`goods_categories` (`good_id`, `category_id`, `is_visible`) VALUES (1, 2, 1);
INSERT INTO `needsandfun`.`goods_categories` (`good_id`, `category_id`, `is_visible`) VALUES (1, 3, 1);

COMMIT;

-- -----------------------------------------------------
-- Data for table `needsandfun`.`property_types`
-- -----------------------------------------------------
START TRANSACTION;
USE `needsandfun`;
INSERT INTO `needsandfun`.`property_types` (`id`, `type`, `name`, `created`) VALUES (1, 'string', 'Строка', NULL);
INSERT INTO `needsandfun`.`property_types` (`id`, `type`, `name`, `created`) VALUES (2, 'number', 'Число', NULL);
INSERT INTO `needsandfun`.`property_types` (`id`, `type`, `name`, `created`) VALUES (3, 'array', 'Набор значений', NULL);
INSERT INTO `needsandfun`.`property_types` (`id`, `type`, `name`, `created`) VALUES (4, 'range', 'Диапазон', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `needsandfun`.`properties`
-- -----------------------------------------------------
START TRANSACTION;
USE `needsandfun`;
INSERT INTO `needsandfun`.`properties` (`id`, `property_type_id`, `name`, `description`, `created`) VALUES (1, 1, 'Ширина, см', NULL, NULL);
INSERT INTO `needsandfun`.`properties` (`id`, `property_type_id`, `name`, `description`, `created`) VALUES (2, 1, 'Высота, см', NULL, NULL);
INSERT INTO `needsandfun`.`properties` (`id`, `property_type_id`, `name`, `description`, `created`) VALUES (3, 1, 'Глубина, см', NULL, NULL);
INSERT INTO `needsandfun`.`properties` (`id`, `property_type_id`, `name`, `description`, `created`) VALUES (4, 3, 'Вариант расположения', NULL, NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `needsandfun`.`type_properties`
-- -----------------------------------------------------
START TRANSACTION;
USE `needsandfun`;
INSERT INTO `needsandfun`.`type_properties` (`id`, `type_id`, `property_id`, `weight`) VALUES (1, 1, 1, 0);
INSERT INTO `needsandfun`.`type_properties` (`id`, `type_id`, `property_id`, `weight`) VALUES (2, 1, 2, 1);
INSERT INTO `needsandfun`.`type_properties` (`id`, `type_id`, `property_id`, `weight`) VALUES (3, 1, 3, 2);
INSERT INTO `needsandfun`.`type_properties` (`id`, `type_id`, `property_id`, `weight`) VALUES (4, 1, 4, 3);

COMMIT;

-- -----------------------------------------------------
-- Data for table `needsandfun`.`property_values`
-- -----------------------------------------------------
START TRANSACTION;
USE `needsandfun`;
INSERT INTO `needsandfun`.`property_values` (`id`, `property_id`, `value`, `description`, `created`) VALUES (1, 1, '300', NULL, NULL);
INSERT INTO `needsandfun`.`property_values` (`id`, `property_id`, `value`, `description`, `created`) VALUES (2, 2, '400', NULL, NULL);
INSERT INTO `needsandfun`.`property_values` (`id`, `property_id`, `value`, `description`, `created`) VALUES (3, 3, '500', NULL, NULL);
INSERT INTO `needsandfun`.`property_values` (`id`, `property_id`, `value`, `description`, `created`) VALUES (4, 4, 'Левый', NULL, NULL);
INSERT INTO `needsandfun`.`property_values` (`id`, `property_id`, `value`, `description`, `created`) VALUES (5, 4, 'Правый', NULL, NULL);
INSERT INTO `needsandfun`.`property_values` (`id`, `property_id`, `value`, `description`, `created`) VALUES (6, 4, 'Средний', NULL, NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `needsandfun`.`goods_properties`
-- -----------------------------------------------------
START TRANSACTION;
USE `needsandfun`;
INSERT INTO `needsandfun`.`goods_properties` (`id`, `good_id`, `property_id`, `value`) VALUES (NULL, 1, 1, '999');
INSERT INTO `needsandfun`.`goods_properties` (`id`, `good_id`, `property_id`, `value`) VALUES (NULL, 1, 2, '888');
INSERT INTO `needsandfun`.`goods_properties` (`id`, `good_id`, `property_id`, `value`) VALUES (NULL, 1, 3, '777');
INSERT INTO `needsandfun`.`goods_properties` (`id`, `good_id`, `property_id`, `value`) VALUES (NULL, 1, 4, '5');

COMMIT;

-- -----------------------------------------------------
-- Data for table `needsandfun`.`page_types`
-- -----------------------------------------------------
START TRANSACTION;
USE `needsandfun`;
INSERT INTO `needsandfun`.`page_types` (`id`, `name`, `created`) VALUES (1, 'Доставка', NULL);
INSERT INTO `needsandfun`.`page_types` (`id`, `name`, `created`) VALUES (2, 'Оплата', NULL);
INSERT INTO `needsandfun`.`page_types` (`id`, `name`, `created`) VALUES (3, 'Таблица размеров', NULL);
INSERT INTO `needsandfun`.`page_types` (`id`, `name`, `created`) VALUES (4, 'О нас', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `needsandfun`.`pages`
-- -----------------------------------------------------
START TRANSACTION;
USE `needsandfun`;
INSERT INTO `needsandfun`.`pages` (`id`, `page_type_id`, `name`, `contents`, `created`) VALUES (1, 1, 'Доставка', NULL, NULL);
INSERT INTO `needsandfun`.`pages` (`id`, `page_type_id`, `name`, `contents`, `created`) VALUES (2, 2, 'Оплата', NULL, NULL);
INSERT INTO `needsandfun`.`pages` (`id`, `page_type_id`, `name`, `contents`, `created`) VALUES (3, 3, 'Таблица размеров', NULL, NULL);
INSERT INTO `needsandfun`.`pages` (`id`, `page_type_id`, `name`, `contents`, `created`) VALUES (4, 4, 'О нас', NULL, NULL);

COMMIT;
