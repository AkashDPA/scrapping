CREATE TABLE `scrapper`.`links_new` (
     `id` INT UNSIGNED NOT NULL AUTO_INCREMENT , 
     `category_id` INT(4) UNSIGNED NOT NULL , 
     `u_id` VARCHAR(16) NULL DEFAULT NULL , 
     `title` INT(128) NULL DEFAULT NULL , 
     `img_src` VARCHAR(128) NULL DEFAULT NULL , 
     `url` VARCHAR(512) NOT NULL , 
     `tags` VARCHAR(256) NULL DEFAULT NULL , 
     `duration` VARCHAR(16) NULL , 
     PRIMARY KEY (`id`), 
     UNIQUE (`url`)
) ENGINE = InnoDB; 
