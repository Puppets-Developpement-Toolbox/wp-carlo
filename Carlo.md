# Carlo

Carlo est avant tout un moteur de templating en php.  
Sa version actuelle est intégrée à la librairie Puppets [https://github.com/Puppets-Developpement-Toolbox/library/tree/develop](https://github.com/Puppets-Developpement-Toolbox/library/tree/develop)  
soit un ensemble de blocs généralistes, réutilisables, basés sur carlo et (définits dans ces maquettes)[https://www.figma.com/design/YAGncUCOUdVUay7mKLAH2x/-LIB--Diapsodie?node-id=2-4&p=f&t=QDyYKzHqjUoKvxHK-0]

# Le moteur

## carlo\_render

```php
function carlo_render(string $tpl, array $args = []):string
```

Permet de charger un template en indiquant son nom et en chargeant des paramètres.

Ex: 

```php
<?= carlo_render('cta', [
  'label' => 'revenir à la page d\'accueil',
  'link' => '/'
]) ?>
```

## carlo\_get

```php
<?php>
function carlo_get(string|null $key = null):mixed
```

Permet de récupérer les paramètres injectés dans le template.

Ex: 

```php
<a href="<?= carlo_get('link') ?>"><?= carlo_get('link') ?></a>
```

À noter qu’un appel sans clé renverra l’ensemble des paramètres chargés dans le template sous la forme d’un tableau tel qu’il a été fourni lors du *carlo\_render*.

**Attention**: carlo n'échappe pas les valeurs, c’est de la responsabilité du développeur d’échapper les paramètres selon les contextes.

## carlo\_img

```php
<?php
function carlo_img(string $key):string
```

Cette méthode sert à générer le code html d’une image en utilisant l’api du cms pour profiter de ces outils d’optimisation.  
*$key* est la clé stockée dans le tableau de paramètre correspondant à une image.  
Par convention les formats sont nommés *\[width\]x\[height\]:\[crop\]* ou *width* et *height* sont respectivement la largeur et la hauteur en pixel et *\[crop\]* le booléen de recadrage. Tous les paramètres sont facultatifs.  
Quelques exemples de valeurs : 

- 100x200 → une image contenu dans un rectangle de 100px par 200px  
- 100x200:1 → une image remplissant un rectangle de 100px par 200px en cropant l’image  
- 100 → une image de 100px de largeur et une hauteur homothétique  
- x200 → une image de 200px de hauteur et une largeur homothétique

Ex : 

```php
<?php
carlo_img('hero_img'); // retourne le code html de l'image attendu dans le paramètre "hero" dans son format d'origine
carlo_img('hero_img', '70x70'); // retourne la même image dans un format 70x70
carlo_img('hero_img', ['70x70', '(min-width: 1024px)' => '1600x900']); // retourne la même image avec un format 1600x900 pour les écrans de plus de 1024px de large
carlo_img(
'hero_img', ['70x70', '(min-width: 1024px)' => '1600x900'], 
'hero_wide_img', ['(min-width: 2024px)' => '1600x900']
); // retourne la même image avec un format 1600x900 pour les écrans de plus de 1024px de large et une autre image en format 1600x900 pour les écrans de plus de 2024px
carlo_img('hero_img', ['class' => 'mr-16']); // dans tous les cas, si le dernier paramètres est un tableau avec une clé "class", c'est ajouté à la balise sous forme d'attribut
```

# Les drivers

Carlo peut être utilisé dans différents contextes grâce à ces drivers.

## Driver Yaml

Le driver Yml est implémenté dans la [librairie Puppets](https://github.com/Puppets-Developpement-Toolbox/library) et n’est utilisé que dans celle-ci pour tester les différents blocs, on peut le trouver ici [https://github.com/Puppets-Developpement-Toolbox/library/blob/develop/src/carlo/YmlDriver.php](https://github.com/Puppets-Developpement-Toolbox/library/blob/develop/src/carlo/YmlDriver.php).
Il se base sur un fichier yaml pour charger le contenu d’une page

## Driver Wordpress

Le driver wordpress est fournit par le plugin wp_carlo (ce dépot), actuellement en version 5.0 [https://github.com/Puppets-Developpement-Toolbox/wp-carlo](https://github.com/Puppets-Developpement-Toolbox/wp-carlo)

Lorsque l’on active ce plugin, il va automatiquement charger le fichier structure.yml à la racine du thème activé pour configurer automatiquement une majeure partie de l’administration de wordpress.

### structure.yml

Le fichier se présente comme ceci 

```yaml
menus:
 menu_machine_name: label

regions:
 region_machine_name: label

# modèle de page pour les pages
templates:
  teplate_machine_name:
    _label: label
    # liste des blocs pour chaque region
    region_machine_name: [bloc list]

types:
  post_type_machine_name:
    wp_args: 
      name: label
      # voir https://developer.wordpress.org/reference/functions/register_post_type/ pour l'ensemble des propriétés
       
    # modèle de page par défaut du type de contenu
    template: 
      _label: label
      region_machine_name: [bloc list]

    # modèles de page supplémentaires (reprend la structure de la section templates)
    templates: [template list]

```

L’entrée menus crée automatiquement les menu dans wordpress.  
L’entrée types crée des types de post custom avec une définition de template par type.  
L’entrée templates crée des modèles de page que l’on peut sélectionner depuis l’édition d’une page ou du custom post type.  
Les bloc list sont définis soit comme une liste de blocs statiques, c’est à dire que ce sera exactement ces blocs dans cet ordre (ex: \[\!load quote, \!load image\_wide\] ), ou une liste dynamique, c’est à dire que le contributeur peut choisir quel bloc il met parmis un liste de blocs sélectionnable et qu’il peut choisir combien il en met (ex: \- \[\!load quote, \!load image\_wide\]).

Note: le mot  clé \!load sert à charger la définition des blocs lorsque celle-ci n’est pas difinie dans structure.yml (c’est à dire à peu près tout le temps)

### Définition d’un composant

Les composants sont définis dans le dossier templates du thème.  
Ils se composent d’un fichier *php* (l’implémentation) et d’un fichier *yml* (la définition).  
Par exemple, un composant créé dans le thème sous le chemin *template/blocs/quote.php* /  *template/blocs/quote.yml* sera identifié par *blocs/quote*.
Le fichier *yml* définit l’ensemble des champs de saisi nécessaires pour alimenter l’implémentation.

Ex:

```yaml
author: text
description:
  _type: wysiwyg
  _label: Citation
  _help: renseignez le texte de la citation
  _required: true
```

La clé peut être définie seulement par son type, dans ce cas son label a la valeur de la clé, soit par un tableau, les clés préfixées par “\_” sont des clés interne au fonctionnement de carlo.

On trouvera la liste des types supportés ici [https://github.com/Puppets-Developpement-Toolbox/wp-carlo/blob/3.x/inc/blocks.php\#L33C1-L55C27](https://github.com/Puppets-Developpement-Toolbox/wp-carlo/blob/3.x/inc/blocks.php#L33C1-L55C27), les clés internes sont dans le  même fichier et dépendent du type de données.

### Définition des formats d’image

Pour redimensionner les images via le moteur de wordpress, il est nécessaire d’enregistrer tous les formats que l’on utilise dans le projet au préalable via la fonction *carlo\_register\_img\_size* qui prend en paramètre une liste de format tel que définit dans la section sur *carlo\_img* (cf: [https://github.com/Puppets-Developpement-Toolbox/wp-carlo/blob/5.x/inc/media.php](https://github.com/Puppets-Developpement-Toolbox/wp-carlo/blob/5.x/inc/media.php))

### Aller plus loin avec les CPT   Les custom post type et les templates de page utilisation

Pour afficher les custom post types créés, on peut soit :

- les utiliser dans une page en utilisant le template créé comme modèle de page, il faut ajouter pour cela le CPT comme section et charger cette section dans le template de page comme cela :

```yaml
templates:
  default:
    _label: Content page
    header:
      - [!load sections/post:[MY-CPT]]
    content: 
      - [!load sections/post:[MY-CPT]]
```

   

- ou les utiliser en créant leur propre template single-\[my-cpt\].php archive-\[my-cpt\].php, cela si on ne souhaite pas créer une page qui listera le CPT   
    
  En exemple, les pages :   
    
- single-\[my-cpt\].php :

```
<?php
ob_start();
$template = match (true) {
  is_home() => "archive",
  is_404() => "error",
  is_page() => get_page_template_slug() ?: "default",
  is_single() => "type_" . get_post_type(),
};

do_action("carlo_prerender", $template);

carlo_render("global/html_start");
#carlo_render("global/header");

if(is_page()) {
  $regions = carlo_structure("templates")[$template];
} elseif(!is_404()) {
  $regions = carlo_structure("types")[get_post_type()]['template'];
}

?>
<main id="main"
      class="flex-1 flex flex-col gap-15
            laptop:gap-37.5">
<?php
  if(!empty($regions)){
    foreach ($regions as $region => $sections) {
      if(!str_starts_with($region, '_')) {
        carlo_render_region($template, $region);
      }
    }
  }
?>
</main>
<?php
carlo_render('global/html_end');
ob_flush();


```

- archive-\[my-cpt\].php :

```
<?php
ob_start();
$template = match (true) {
  is_home() => "archive",
  is_404() => "error",
  is_page() => get_page_template_slug() ?: "default",
  is_archive() => "type_" . get_post_type(),
};

carlo_render("global/html_start");

$post_type = get_post_type();

$args = array(
    'post_type'      => $post_type,
    'posts_per_page' => 20,       // -1 pour tout récupérer
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
);
$news_query = new WP_Query($args);
?>

<main id="main"
      class="flex-1 flex flex-col gap-15
            laptop:gap-37.5">

<?php
if ($news_query->have_posts()) :
    echo '<ul class="news-list">';
    while ($news_query->have_posts()) : $news_query->the_post();
        printf(
            '<li><a href="%s">%s</a> <small>%s</small></li>',
            esc_url(get_permalink()),
            esc_html(get_the_title()),
            esc_html(get_the_date())
        );
    endwhile;
    echo '</ul>';
    wp_reset_postdata();
else :
    echo '<p>Aucune news trouvée.</p>';
endif;
?>

</main>

<?php
carlo_render('global/html_end');
ob_flush();
```

     

### Mais encore

Le thème de base fournit quelques fonctionnalités supplémentaires.

Tous les fichiers *php* présents dans le dossier *inc* du thème enfant sont automatiquement chargés.

Le module vite est configuré et adapté au fichier [vite.config.js](http://vite.config.js) fournie par la librairie (elle même en dépendance de du thème.

Il y a une page spécifique pour afficher les erreurs en développement.

Gutemberg, les pings, les commentaires est désactivé, l’admin d’acf est masqué en prod et synchronisé entre les environnements.
