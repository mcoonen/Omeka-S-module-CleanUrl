��    G      T  a   �             �     }   �  [   >     �     �  p   �     ,     =  S   J  S   �  �   �     x	  ,   �	  <   �	     
  ,   
  0   5
  ,   f
     �
     �
     �
  !   �
          /     F     c     }     �     �  
   �     �  $   �  ]     x   b  Q   �  L   -  .   z  W   �  �     P   �  ?   �  H   :  ;   �  ;   �  I   �  <   E  0   �  $   �  O   �  U   (  N   ~  P   �  M     M   l  S   �  ;     3   J  "   ~  %   �  u   �  �   =  b   �  �   .  �        �     �     �     �     �  #  �     �  �   �  �   �  `   a     �     �  �   �     s     �  d   �  ^     �   a  +   �  6   $  @   [     �  9   �  =   �  :     ,   W  $   �  %   �  (   �  %   �        &   ?  #   f     �  #   �     �     �     �  *     |   ,  �   �  c   4   j   �   >   !  `   B!  �   �!  L   f"  N   �"  I   #  K   L#  H   �#  H   �#  J   *$  G   u$  *   �$  d   �$  p   M%  \   �%  ^   &  ^   z&  f   �&  c   @'  R   �'  @   �'  $   8(  "   ](  �   �(  �   )  y   �)    G*  �   S+  !   �+     ,     ,     &,     ,,     E      <   #             .   '       :         >       /          "       *   6   &         @   8           G   =   (   	       ,           D       F       9      ?          -                      
              $      !   B                   A      7              3   +      4                           2      %   )          ;   1      0          5   C    ", " "Clean Url" module allows to have clean, readable and search engine optimized urls for pages and resources, like https://example.net/item_set_identifier/item_identifier. %sWarning%s: the config of the module cannot be saved in "config/cleanurl.config.php". It is required to skip the site paths. A pattern for "%s", for example "[a-zA-Z][a-zA-Z0-9_-]*", is required to use the path "%s". Additional paths Admin Interface Check the new config file "config/clean_url.config.php" and remove the old one in the config directory of Omeka. Clean identifier Default path Field where the identifier of the resource is set. Default is "dcterms:identifier". For a good seo, it’s not recommended to have multiple urls for the same resource. For identifiers, it is recommended to use a pattern that includes at least one letter to avoid confusion with internal numerical ids. Identifiers are case sensitive Identifiers have slash, so don’t escape it Item should be an Item, an ItemRepresentation or an integer. Medias Optional pattern of a media short identifier Optional pattern of an item set short identifier Optional pattern of an item short identifier Other reserved routes in admin Pattern of a media identifier Pattern of an item identifier Pattern of an item set identifier Prefix to select an identifier Property of identifier Rename or skip prefix /page/ Rename or skip prefix /s/ Resource identifiers See %s for more information. Select a property… Short path Sites and pages Skip "s/site-slug/" for default site Some formats of short identifiers are case sensitive, so search will be done in a binary way. The file "clean_url.config.php" and/or "config/clean_url.dynamic.php" in the config directory of Omeka is not writeable. The file "cleanurl.config.php" in the config directory of Omeka is not writeable. The file "config/cleanurl.config.php" at the root of Omeka is not writeable. The main site is defined in the main settings. The module "%s" was automatically deactivated because the dependencies are unavailable. The module has been rewritten and the whole configuration has been simplified. You should check your config, because the upgrade of the configuration is not automatic. The path "%s" for item sets should contain one and only one item set identifier. The path "%s" for item sets should not contain identifier "%s". The path "%s" for items should contain one and only one item identifier. The path "%s" for items should not contain identifier "%s". The path "%s" for medias should contain an item identifier. The path "%s" for medias should contain one and only one item identifier. The path "%s" for medias should not contain identifier "%s". The path is an unquoted regex without site slug. The prefix is part of the identifier The site pages "%s" use a reserved string and "/s/site-slug" cannot be skipped. The site pages "%s" use a reserved string and the prefix for pages cannot be removed. The sites "%s" use a reserved string and the "/s/site-slug" cannot be skipped. The sites "%s" use a reserved string and the prefix for sites cannot be removed. The slug "%s" is used or reserved and the prefix for pages cannot be updated. The slug "%s" is used or reserved and the prefix for sites cannot be updated. The slug "%s" is used or reserved. A random string has been automatically appended. There is no default site: "/s/site-slug" cannot be skipped. This module has resources that connot be installed. This module requires modules "%s". This module requires the module "%s". This option allows to fix routes for unmanaged modules. Add them in the file cleanurl.config.php or here, one by row. This option is required if you have ark and you choose to manage them as a whole, without the fix part. Check the pattern of identifiers too. This option is required to get the whole identifier when needed, for example with the IIIF server. This prefix allows to find one identifier when there are multiple values: "ark:", "record:", or "doc =". Include space if needed. Let empty to use the first identifier. If this identifier does not exists, the Omeka item id will be used. Unable to copy config files "config/clean_url.config.php" and/or "config/clean_url.dynamic.php" in the config directory of Omeka. Use in admin board [none] ark:/12345/ page/ s/ Project-Id-Version: 
Report-Msgid-Bugs-To: 
PO-Revision-Date: 2020-11-09 00:00+0000
Last-Translator: Daniel Berthereau <Daniel.fr@Berthereau.net>
Language-Team: 
Language: fr
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
X-Generator: Poedit 2.4.1
 ", " Le module "Clean Url" permet de diffuser des urls claires, lisibles, et optimisées pour les moteurs de recherche pour les pages et les ressources, comme http://exemple.com/ma_collection/id_contenu. %sAttention%s: la configuration du module ne peut pas être enregistrée dans "config/cleanurl.config.php". Cela est nécessaire pour ignorer les chemins des sites. Un modèle pour "%s", par exemple "[a-zA-Z][a-zA-Z0-9_-]*", est nécessaire pour le chemin "%s". Chemins complémentaires Interface admin Vérifier le nouveau fichier de configuration "config/clean_url.config.php" et supprimer l’ancien dans le dossier "config" d’Omeka. Identifiant normalisé Chemin par défaut Propriété dans laquelle trouver l’identifiant de la ressource. Défaut : "dcterms:identifier". Pour un bon SEO, il n’est pas recommandé d’avoir plusieurs urls pour une même ressource. Pour les identifiants, il est recommandé d’utiliser un modèle qui inclut au moins une lettre pour éviter la confusion avec les numéros internes. Les identifiants sont sensibles à la casse Les identifiants ont une barre "/" à ne pas échapper L’item doit être un Item, un ItemRepresentation ou un entier. Médias Modèle facultatif pour l’identifiant court des médias Modèle facultatif pour l’identifiant court des collections Modèle facultatif pour l’identifiant court des contenus Autres routes réservées en interface admin Modèle d’un identifiant de média Modèle d’un identifiant de contenu Modèle d’un identifiant de collection Préfixe pour trouver l’identifiant Propriété pour l’identifiant Renommer ou ignorer le préfixe /page/ Renommer ou ignorer le préfixe /s/ Identifiants des ressources Voir %s pour plus d’informations. Choisir une propriété… Chemin court Sites et pages Enlever "s/site-slug/" du site par défaut Certains formats d’identifiants courts sont sensibles à la casse et la recherche doit donc s’effectuer en mode binaire. Le fichier "clean_url.config.php" ou "config/clean_url.dynamic.php" dans le dossier de configuration d’Omeka ne peut pas être modifié. Le fichier "cleanurl.config.php" dans le dossier de configuration d’Omeka n’est pas modifiable. Le fichier "config/cleanurl.config.php" dans le dossier de configuration d’Omeka n’est pas modifiable. Le site principal est défini dans les paramètres généraux. Le module %s" a été automatiquement désactivé car ses dépendances ne sont plus disponibles. Le module a été entièrement réécrit et l’ensemble de la configuration a été simplifiée. Il est recommandé de vérifier la configuration car la mise à jour n’ est pas automatique. Le chemin "%s" pour les collections doit contenir un identifiant et un seul. Le chemin "%s" pour les collections ne doit pas contenir l’identifiant "%s". Le chemin "%s" pour les contenus doit contenir un identifiant et un seul. Le chemin "%s" pour les contenus ne doit pas contenir l’identifiant "%s". Le chemin "%s" pour les médias doit contenir un identifiant du contenu. Le chemin "%s" pour les médias doit contenir un identifiant et un seul. Le chemin "%s" pour les médias ne doit pas contenir l’identifiant "%s". Le chemin est un modèle regex non échappé et sans le chemin du site. Le préfixe fait partie de l’identifiant Les pages de site "%s" utilisent une chaîne réservée et "/s/site-slug" ne peut pas être enlevé. Les pages de site "%s" utilisent une chaîne réservée et le préfixe pour les pages ne peut pas être enlevé. Les sites "%s" utilisent une chaîne réservée et "/s/site-slug" ne peut pas être enlevé. Les sites "%s" utilisent une chaîne réservée et le préfixe pour les sites ne peut enlevé. Le segment "%s" est utilisé ou réservé et le préfixe des pages ne peut pas être modifié. Le segment "%s" est utilisé ou réservé et le préfixe pour les sites ne peut pas être mis à jour. Le segment "%s" est utilisé ou réservé. Une chaîne aléatoire a été automatiquement ajoutée. Aucun site par défaut n'est défini : "/s/site-slug" ne peut pas être enlevé. Ce module a des ressources qui ne peuvent pas être installées. Ce module requiert les modules "%s". Ce module requiert le module "%s". Cette option permet de corriger les routes pour les modules non gérés. On peut les ajouter dans le fichier "cleanurl.config.php" ou ici, une par ligne. Cette option est nécessaire si vous utilisez ark et que vous les gérez en tant qu’identifiant unique, sans la partie fixe. Vérifiez les modèles des identifiants également. Cette option est nécessaire pour disposer de l’identifiant complet en cas de besoin, par exemple pour le serveur IIIF. Ce préfixe permet de trouver un identifiant quand une ressource en a plusieurs : "ark:", "notice:", or "doc =". Inclure l’espace si besoin. Laisser vide pour utiliser le premier identifiant. Si l’identifiant n’existe pas, l’identifiant Omeka sera utilisé. Impossible de copier le fichier de configuration "config/clean_url.config.php" ou "config/clean_url.dynamic.php" dans le dossier de configuration d’Omeka. Utiliser dans l’interface admin [aucun] ark:/12345/ page/ s/ 