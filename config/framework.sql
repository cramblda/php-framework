CREATE TABLE IF NOT EXISTS `auth_role` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `auth_role` (`id`, `name`, `description`) VALUES
(1, 'ROLE_USER', 'Basic user functionality'),
(2, 'ROLE_ADMIN', 'Administrative functionality');



CREATE TABLE IF NOT EXISTS `auth_user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `salt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `locked` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO `auth_user` (`id`, `username`, `email`, `password`, `salt`, `first_name`, `last_name`, `active`, `locked`) VALUES
(1, 'admin', 'admin@sample.org', 'cafb85946b3f7170257293428e4bbde2', 'ceb20772e0c9d240c75eb26b0e37abee', 'Admin', 'User', 1, 0);



CREATE TABLE IF NOT EXISTS `auth_user__auth_role` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `auth_user__auth_role` (`user_id`, `role_id`) VALUES
(1, 1),
(1, 2);



ALTER TABLE `auth_role`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `auth_role__name__ukey` (`name`);

ALTER TABLE `auth_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `auth_user__username__ukey` (`username`);

ALTER TABLE `auth_user__auth_role`
  ADD KEY `auth_user__auth_role__user_id__key` (`user_id`),
  ADD KEY `auth_user__auth_role__role_id__key` (`role_id`);


ALTER TABLE `auth_role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;

ALTER TABLE `auth_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;

ALTER TABLE `auth_user__auth_role`
  ADD CONSTRAINT `auth_role__id__fkey` FOREIGN KEY (`role_id`) REFERENCES `auth_role` (`id`),
  ADD CONSTRAINT `auth_user__id__fkey` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`);
