# Flow Framework Connector to Gedmo Translatable

by Sebastian Kurfürst, sandstorm|media. Thanks to WebEssentials for sponsoring this work.

Using Gedmo.Translatable in Flow Framework proved a little harder than originally anticipated -- that's why I created
a small package which wraps up the necessary steps.

Has been tested on recent Flow Framework master.


## Usage

Just include this package, and then use Gedmo Translatable as explained in their documentation (e.g. using
the @Gedmo\Translatable annotation): https://github.com/Atlantic18/DoctrineExtensions/blob/master/doc/translatable.md

Make sure to clear the code cache completely in Data/Temporary after installing this package!

Furthermore, make sure to create a *doctrine migration* which creates the ext_translations SQL table; e.g. run `./flow doctrine:migrationgenerate`

**Check out the example package at https://github.com/sandstorm/GedmoTest**.

### Model Annotations

Just annotate your model properties which shall be localized with `Gedmo\Mapping\Annotation\Translatable`.

### Translating a model (low-level)

```
	/**
	 * Doctrine's Entity Manager. Note that "ObjectManager" is the name of the related interface.
	 *
	 * @Flow\Inject
	 * @var ObjectManager
	 */
	protected $entityManager;

	public function updateAction(Event $event) {
		/* @var $repository TranslationRepository */
		$repository = $this->entityManager->getRepository('Gedmo\\Translatable\\Entity\\Translation');
		$repository->translate($event, 'name', 'de', 'Deutscher Titel');
	}
```

### Set current language

In order to set the current language for *viewing*, inject the `Gedmo\Translatable\TranslatableListener` class and set
the current language on it: `$translatableListener->setTranslatableLocale('de');`.

### Editing multiple languages at the same time

* Mix-in the Trait `Sandstorm\GedmoTranslatableConnector\TranslatableTrait` into your model, e.g. by doing:

```
/**
 * @Flow\Entity
 */
class MyModel {
  use \Sandstorm\GedmoTranslatableConnector\TranslatableTrait;
  
  // make sure some properties have Gedmo\Translatable annotations
}
```

* This trait adds a `getTranslations()` and `setTranslations()` method, allowing to get and set other translations of
  a model.
  
* Now, you can easily edit multiple languages by binding the form element to `translations.[language].[fieldname]`, e.g.
  this works like the following:

```
Name (default): <f:form.textfield property="name" /><br />
Name (de): <f:form.textfield property="translations.de.name" /><br />
Name (en): <f:form.textfield property="translations.en.name" /><br />
```

### Fetching an object in another locale

If you have loaded an object in a specific locale, but lateron need to change the object to be in another locale,
the method `reloadInLocale($locale)` (which is defined inside the trait `Sandstorm\GedmoTranslatableConnector\TranslatableTrait`)
can be called:

```
$myModel->getName(); // will return the language which was set at the time where $myModel was fetched

$myModel->reloadInLocale('de');
$myModel->getName(); // will return *german*
```

**NOTE**: This has only been tested as long as these objects are not updated.


## Translating Associations

**Warning: this feature is not yet 100% stable; please test it and give feedback!**

Normally, associations towards other domain models such as images or assets are not translation-aware; but Translatable
only works for simple properties.

The TranslatableConnector however contains some functionality to make translation of associations work; by using a
little workaround: **We store the identifier of the target object in the domain model, and manually load/store from this
identifier**.

This works as follows:

1. Makes sure you have the `TranslatableTrait` added to your domain class

2. e.g. to make an `Asset` reference translatable, create a new property `assetIdentifer` which is a string and will
   contain the asset identifier. This property should be marked as `Gedmo\Translatable`.

3. Then, you need to configure the `translationAssociationMapping`, which tells the system that the (virtual) property
   should `asset` should internally be stored as `assetIdentifier`.

4. Furthermore, create `assetOnSave` and `assetOnLoad` methods as outlined below, which convert the different representations

See the full example below:

```
class Event {
	use TranslatableTrait;
	
	/**
	 * @var array
	 * @Flow\Transient
	 */
	protected $translationAssociationMapping = array(
		'assetIdentifier' => 'asset'
	);
	
	/**
	 * @Gedmo\Translatable
	 * @var string
	 */
	protected $assetIdentifier;

	/**
	 * @Flow\Inject
	 * @var AssetRepository
	 */
	protected $assetRepository;

	/**
	 * @Flow\Inject
	 * @var PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @Flow\Inject
	 * @var PropertyMapper
	 */
	protected $propertyMapper;


	/**
	 * @return \TYPO3\Media\Domain\Model\Asset
	 */
	public function getAsset() {
		return $this->assetOnLoad($this->assetIdentifier);
	}

	/**
	 * !!! This accepts the raw array as the user uploaded it; as we need to trigger the property mapper inside
	 *     assetOnSave manually.
	 *
	 * @param array $asset
	 */
	public function setAsset($asset) {
		$this->assetIdentifier = $this->assetOnSave($asset);
	}

	/**
	 * This method is called in two places:
	 * - inside setAsset()
	 * - automatically by the TranslatableTrait
	 * 
	 * @param array $asset
	 */
	public function assetOnSave($asset) {
		$asset = $this->propertyMapper->convert($asset, 'TYPO3\Media\Domain\Model\AssetInterface');
		if ($asset === NULL) {
			$this->assetRepository->remove($asset);
			return NULL;
		} elseif ($this->persistenceManager->isNewObject($asset)) {
			$this->assetRepository->add($asset);
			return $this->persistenceManager->getIdentifierByObject($asset);
		} else {
			$this->assetRepository->update($asset);
			return $this->persistenceManager->getIdentifierByObject($asset);
		}
	}

	/**
	 * This method is called in two places:
	 * - inside getAsset()
	 * - automatically by the TranslatableTrait
	 * 
	 * @param array $asset
	 */
	public function assetOnLoad($assetIdentifier) {
		return $this->assetRepository->findByIdentifier($assetIdentifier);
	}
}
```   


## Inner Workings

(as a further reference -- could also be reduced if we change Flow Framework a little on the relevant parts)

* Settings.yaml: Ignore Gedmo namespace from Reflection, adds the Translatable Listener as Doctrine Event listener.
  This part is pretty standard for using other Gedmo Doctrine extensions as well.
  
* Objects.yaml: Mark the `TranslatableListener` as singleton, such that you can inject it into your classes and set
  the current language. This part was straightforward as well.
  
* Package.php: Make the entities of Gedmo Translatable known to Doctrine and in Reflection. This was quite tricky to
  archive, see the inline docs in the class how this was done.


## TODO list

* check Flow Framework 2.2