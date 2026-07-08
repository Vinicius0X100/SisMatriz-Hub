CREATE TABLE `atendimento_filas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `paroquia_id` bigint(20) unsigned NOT NULL,
  `data` date NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `atendimento_filas_paroquia_id_data_index` (`paroquia_id`,`data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `atendimento_fila_itens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fila_id` bigint(20) unsigned NOT NULL,
  `register_id` bigint(20) unsigned DEFAULT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `assunto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hora_agendada` time DEFAULT NULL,
  `tipo` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `telefone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_enviado` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `atendimento_fila_itens_fila_id_status_index` (`fila_id`,`status`),
  KEY `atendimento_fila_itens_fila_id_tipo_index` (`fila_id`,`tipo`),
  CONSTRAINT `atendimento_fila_itens_fila_id_foreign` FOREIGN KEY (`fila_id`) REFERENCES `atendimento_filas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
