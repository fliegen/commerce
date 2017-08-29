<?php

namespace Drupal\Tests\commerce_order\FunctionalJavascript;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Url;
use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;
use Drupal\Tests\commerce\FunctionalJavascript\JavascriptTestTrait;

/**
 * Tests the ProfileSelect form element.
 *
 * @group commerce
 */
class MultipleProfileSelectTest extends CommerceBrowserTestBase {

  use JavascriptTestTrait;

  /**
   * Profile address values.
   *
   * @var array
   */
  protected $address1 = [
    'country_code' => 'HU',
    'given_name' => 'Gustav',
    'family_name' => 'Mahler',
    'address_line1' => 'Teréz körút 7',
    'locality' => 'Budapest',
    'postal_code' => '1067',
  ];

  /**
   * Profile address values.
   *
   * @var array
   */
  protected $address2 = [
    'country_code' => 'DE',
    'given_name' => 'Johann Sebastian',
    'family_name' => 'Bach',
    'address_line1' => 'Thomaskirchhof 15',
    'locality' => 'Leipzig',
    'postal_code' => '04109',
  ];

  protected $address3 = [
    'country_code' => 'TW',
    'postal_code' => '103',
    'administrative_area' => 'Taipei City',
    'locality' => 'Datong District',
    'address_line1' => 'No.4, Huating St.',
    'given_name' => 'Chris',
    'family_name' => 'Wu',
  ];

  /**
   * @var \Drupal\profile\ProfileStorageInterface
   */
  protected $profileStorage;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'commerce_order_test',
  ];

  /**
   * @inheritDoc
   */
  protected function setUp() {
    parent::setUp();
    $this->profileStorage = $this->container->get('entity_type.manager')->getStorage('profile');
  }

  /**
   * Tests the profile select form element for anonymous user.
   */
  public function testAnonymous() {
    $this->drupalLogout();
    $address1_fields = $this->address1;
    $address3_fields = $this->address3;
    $this->drupalGet(Url::fromRoute('commerce_order_test.multiple_profile_select_form'));
    $this->assertSession()->statusCodeEquals(200);

    $this->assertSession()->fieldNotExists('Select a profile');
    $this->getSession()->getPage()->fillField('profile1[address][0][address][country_code]', $address1_fields['country_code']);
    $this->waitForAjaxToFinish();
    $this->getSession()->getPage()->fillField('profile2[address][0][address][country_code]', $address3_fields['country_code']);
    $this->waitForAjaxToFinish();
    $this->getSession()->getPage()->fillField('profile2[address][0][address][administrative_area]', $address3_fields['administrative_area']);
    $this->waitForAjaxToFinish();
    $this->getSession()->getPage()->fillField('profile2[address][0][address][locality]', $address3_fields['locality']);
    $this->waitForAjaxToFinish();

    $this->createScreenshot();

    $edit = [];
    foreach ($address1_fields as $key => $value) {
      if ($key == 'country_code') {
        continue;
      }
      $edit['profile1[address][0][address][' . $key . ']'] = $value;
    }

    foreach ($address3_fields as $key => $value) {
      if ($key == 'country_code' || $key == 'administrative_area' || $key == 'locality') {
        continue;
      }
      $edit['profile2[address][0][address][' . $key . ']'] = $value;
    }

    $this->submitForm($edit, 'Submit');

    /** @var \Drupal\profile\Entity\ProfileInterface $profile1 */
    $profile1 = $this->profileStorage->load(1);
    $profile2 = $this->profileStorage->load(2);

    $this->assertSession()->responseContains(new FormattableMarkup('Profile1 selected: :label', [':label' => $profile1->label()]));
    $this->assertSession()->responseContains(new FormattableMarkup('Profile2 selected: :label', [':label' => $profile2->label()]));


    /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $address1 */
    $address1 = $profile1->get('address')->first();
    $this->assertEquals($address1_fields['country_code'], $address1->getCountryCode());
    $this->assertEquals($address1_fields['given_name'], $address1->getGivenName());
    $this->assertEquals($address1_fields['family_name'], $address1->getFamilyName());
    $this->assertEquals($address1_fields['address_line1'], $address1->getAddressLine1());
    $this->assertEquals($address1_fields['locality'], $address1->getLocality());
    $this->assertEquals($address1_fields['postal_code'], $address1->getPostalCode());

    /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $address2 */
    $address2 = $profile2->get('address')->first();
    $this->assertEquals($address3_fields['country_code'], $address2->getCountryCode());
    $this->assertEquals($address3_fields['given_name'], $address2->getGivenName());
    $this->assertEquals($address3_fields['family_name'], $address2->getFamilyName());
    $this->assertEquals($address3_fields['address_line1'], $address2->getAddressLine1());
    $this->assertEquals($address3_fields['locality'], $address2->getLocality());
    $this->assertEquals($address3_fields['postal_code'], $address2->getPostalCode());
  }

  /**
   * Tests the profile select form element for anonymous user.
   */
  public function testAuthenticatedNoExistingProfiles() {
    $account = $this->createUser();
    $this->drupalLogin($account);

    $address1_fields = $this->address1;
    $address3_fields = $this->address3;
    $this->drupalGet(Url::fromRoute('commerce_order_test.multiple_profile_select_form'));
    $this->assertSession()->statusCodeEquals(200);

    $this->assertSession()->fieldNotExists('Select a profile');
    $this->getSession()->getPage()->fillField('Country', $address1_fields['country_code']);
    $this->waitForAjaxToFinish();
    $this->getSession()->getPage()->fillField('profile2[address][0][address][country_code]', $address3_fields['country_code']);
    $this->waitForAjaxToFinish();
    $this->getSession()->getPage()->fillField('profile2[address][0][address][administrative_area]', $address3_fields['administrative_area']);
    $this->waitForAjaxToFinish();
    $this->getSession()->getPage()->fillField('profile2[address][0][address][locality]', $address3_fields['locality']);
    $this->waitForAjaxToFinish();

    $edit = [];
    foreach ($address1_fields as $key => $value) {
      if ($key == 'country_code') {
        continue;
      }
      $edit['profile1[address][0][address][' . $key . ']'] = $value;
    }

    foreach ($address3_fields as $key => $value) {
      if ($key == 'country_code' || $key == 'administrative_area' || $key == 'locality') {
        continue;
      }
      $edit['profile2[address][0][address][' . $key . ']'] = $value;
    }

    $this->submitForm($edit, 'Submit');

    /** @var \Drupal\profile\Entity\ProfileInterface $profile1 */
    $profile1 = $this->profileStorage->load(1);
    $profile2 = $this->profileStorage->load(2);

    $this->assertSession()->responseContains(new FormattableMarkup('Profile1 selected: :label', [':label' => $profile1->label()]));
    $this->assertSession()->responseContains(new FormattableMarkup('Profile2 selected: :label', [':label' => $profile2->label()]));


    /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $address1 */
    $address1 = $profile1->get('address')->first();
    $this->assertEquals($address1_fields['country_code'], $address1->getCountryCode());
    $this->assertEquals($address1_fields['given_name'], $address1->getGivenName());
    $this->assertEquals($address1_fields['family_name'], $address1->getFamilyName());
    $this->assertEquals($address1_fields['address_line1'], $address1->getAddressLine1());
    $this->assertEquals($address1_fields['locality'], $address1->getLocality());
    $this->assertEquals($address1_fields['postal_code'], $address1->getPostalCode());

    /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $address2 */
    $address2 = $profile2->get('address')->first();
    $this->assertEquals($address3_fields['country_code'], $address2->getCountryCode());
    $this->assertEquals($address3_fields['given_name'], $address2->getGivenName());
    $this->assertEquals($address3_fields['family_name'], $address2->getFamilyName());
    $this->assertEquals($address3_fields['address_line1'], $address2->getAddressLine1());
    $this->assertEquals($address3_fields['locality'], $address2->getLocality());
    $this->assertEquals($address3_fields['postal_code'], $address2->getPostalCode());
  }

  /**
   * Tests the profile select form element for authenticated user.
   */
  public function testProfileSelectAuthenticated() {
    $account = $this->createUser();

    /** @var \Drupal\profile\Entity\ProfileInterface $profile_address1 */
    $profile_address1 = $this->createEntity('profile', [
      'type' => 'customer',
      'uid' => $account->id(),
      'address' => $this->address1,
    ]);
    /** @var \Drupal\profile\Entity\ProfileInterface $profile_address2 */
    $profile_address2 = $this->createEntity('profile', [
      'type' => 'customer',
      'uid' => $account->id(),
      'address' => $this->address2,
      'is_default' => TRUE,
    ]);

    $this->drupalLogin($account);
    $this->drupalGet(Url::fromRoute('commerce_order_test.multiple_profile_select_form'));

    $this->createScreenshot();

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldExists('profile1[profile_selection]');
    $this->assertSession()->fieldExists('profile2[profile_selection]');
    // The last created profile should be selected by default.
    $profile1_selector = '//div[@data-drupal-selector="edit-profile1"]';
    $profile2_selector = '//div[@data-drupal-selector="edit-profile2"]';
    $this->assertSession()->elementTextContains('xpath', $profile1_selector, $this->address2['locality']);
    $this->assertSession()->elementTextContains('xpath', $profile2_selector, $this->address2['locality']);

    $this->getSession()->getPage()->fillField('profile1[profile_selection]', $profile_address1->id());
    $this->waitForAjaxToFinish();
    $this->assertSession()->elementTextContains('xpath', $profile1_selector, $this->address1['locality']);

    $this->getSession()->getPage()->fillField('profile2[profile_selection]', $profile_address2->id());
    $this->waitForAjaxToFinish();
    $this->assertSession()->elementTextContains('xpath', $profile2_selector, $this->address2['locality']);

    $this->submitForm([], 'Submit');
    $this->assertSession()->responseContains(new FormattableMarkup('Profile1 selected: :label', [':label' => $profile_address1->label()]));
    $this->assertSession()->responseContains(new FormattableMarkup('Profile2 selected: :label', [':label' => $profile_address2->label()]));


    $this->profileStorage->resetCache([$profile_address1->id()]);
    $this->profileStorage->resetCache([$profile_address2->id()]);
    $profile_address1 = $this->profileStorage->load($profile_address1->id());
    $profile_address2 = $this->profileStorage->load($profile_address2->id());
    /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $address1 */
    $address1 = $profile_address1->get('address')->first();
    /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $address2 */
    $address2 = $profile_address2->get('address')->first();
    // Assert that field values have not changed.
    $this->assertEquals($this->address1['country_code'], $address1->getCountryCode());
    $this->assertEquals($this->address1['given_name'], $address1->getGivenName());
    $this->assertEquals($this->address1['family_name'], $address1->getFamilyName());
    $this->assertEquals($this->address1['address_line1'], $address1->getAddressLine1());
    $this->assertEquals($this->address1['locality'], $address1->getLocality());
    $this->assertEquals($this->address1['postal_code'], $address1->getPostalCode());

    $this->assertEquals($this->address2['country_code'], $address2->getCountryCode());
    $this->assertEquals($this->address2['given_name'], $address2->getGivenName());
    $this->assertEquals($this->address2['family_name'], $address2->getFamilyName());
    $this->assertEquals($this->address2['address_line1'], $address2->getAddressLine1());
    $this->assertEquals($this->address2['locality'], $address2->getLocality());
    $this->assertEquals($this->address2['postal_code'], $address2->getPostalCode());
  }

  /**
   * Tests the profile select form element for authenticated user.
   */
  public function testProfileSelectAuthenticatedCreateNew() {
    $account = $this->createUser();
    $address2_fields = $this->address2;
    $address3_fields = $this->address3;
    /** @var \Drupal\profile\Entity\ProfileInterface $profile_address1 */
    $profile_address1 = $this->createEntity('profile', [
      'type' => 'customer',
      'uid' => $account->id(),
      'address' => $this->address1,
    ]);

    $this->drupalLogin($account);
    $this->drupalGet(Url::fromRoute('commerce_order_test.multiple_profile_select_form'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldExists('profile1[profile_selection]');
    $this->assertSession()->fieldExists('profile2[profile_selection]');
    // The last created profile should be selected by default.
    $profile1_selector = '//div[@data-drupal-selector="edit-profile1"]';
    $profile2_selector = '//div[@data-drupal-selector="edit-profile2"]';
    $this->assertSession()->elementTextContains('xpath', $profile1_selector, $this->address1['locality']);
    $this->assertSession()->elementTextContains('xpath', $profile2_selector, $this->address1['locality']);

    $this->getSession()->getPage()->fillField('profile1[profile_selection]', '_new');
    $this->waitForAjaxToFinish();
    $this->getSession()->getPage()->fillField('Country', $address2_fields['country_code']);
    $this->waitForAjaxToFinish();
    $edit = [];
    foreach ($address2_fields as $key => $value) {
      if ($key == 'country_code') {
        continue;
      }
      $edit['profile1[address][0][address][' . $key . ']'] = $value;
    }

    $this->getSession()->getPage()->fillField('profile2[profile_selection]', '_new');
    $this->waitForAjaxToFinish();
    $this->getSession()->getPage()->fillField('profile2[address][0][address][country_code]', $address3_fields['country_code']);
    $this->waitForAjaxToFinish();
    $this->getSession()->getPage()->fillField('profile2[address][0][address][administrative_area]', $address3_fields['administrative_area']);
    $this->waitForAjaxToFinish();
    $this->getSession()->getPage()->fillField('profile2[address][0][address][locality]', $address3_fields['locality']);
    $this->waitForAjaxToFinish();
    foreach ($address3_fields as $key => $value) {
      if ($key == 'country_code' || $key == 'administrative_area' || $key == 'locality') {
        continue;
      }
      $edit['profile2[address][0][address][' . $key . ']'] = $value;
    }

    $this->submitForm($edit, 'Submit');

    $new_profile2 = $this->profileStorage->load(2);
    $new_profile3 = $this->profileStorage->load(3);
    /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $address2 */
    $address2 = $new_profile2->get('address')->first();
    /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $address3 */
    $address3 = $new_profile3->get('address')->first();

    $this->assertSession()->responseContains(new FormattableMarkup('Profile1 selected: :label', [':label' => $new_profile2->label()]));
    $this->assertSession()->responseContains(new FormattableMarkup('Profile2 selected: :label', [':label' => $new_profile3->label()]));
    // Assert that field values have not changed.
    $this->assertEquals($this->address2['country_code'], $address2->getCountryCode());
    $this->assertEquals($this->address2['given_name'], $address2->getGivenName());
    $this->assertEquals($this->address2['family_name'], $address2->getFamilyName());
    $this->assertEquals($this->address2['address_line1'], $address2->getAddressLine1());
    $this->assertEquals($this->address2['locality'], $address2->getLocality());
    $this->assertEquals($this->address2['postal_code'], $address2->getPostalCode());

    $this->assertEquals($this->address3['country_code'], $address3->getCountryCode());
    $this->assertEquals($this->address3['given_name'], $address3->getGivenName());
    $this->assertEquals($this->address3['family_name'], $address3->getFamilyName());
    $this->assertEquals($this->address3['address_line1'], $address3->getAddressLine1());
    $this->assertEquals($this->address3['locality'], $address3->getLocality());
    $this->assertEquals($this->address3['postal_code'], $address3->getPostalCode());
  }

  /**
   * Tests the profile select form element for authenticated user.
   *
   * @group debug
   */
  public function testProfileSelectAuthenticatedEdit() {
    $account = $this->createUser();
    /** @var \Drupal\profile\Entity\ProfileInterface $profile_address1 */
    $profile_address1 = $this->createEntity('profile', [
      'type' => 'customer',
      'uid' => $account->id(),
      'address' => $this->address1,
    ]);
    /** @var \Drupal\profile\Entity\ProfileInterface $profile_address2 */
    $profile_address2 = $this->createEntity('profile', [
      'type' => 'customer',
      'uid' => $account->id(),
      'address' => $this->address2,
      'is_default' => TRUE,
    ]);

    $this->drupalLogin($account);
    $this->drupalGet(Url::fromRoute('commerce_order_test.multiple_profile_select_form'));
    $this->assertSession()->statusCodeEquals(200);

    // Edit a profile.
    $this->drupalGet(Url::fromRoute('commerce_order_test.multiple_profile_select_form'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldExists('profile1[profile_selection]');
    // The last created profile should be selected by default.
    $this->assertSession()->pageTextContains($this->address2['locality']);
    $this->getSession()->getPage()->pressButton('profile1_edit_profile');
    $this->waitForAjaxToFinish();

    foreach ($this->address2 as $key => $value) {
      $this->assertSession()->fieldValueEquals('profile1[address][0][address][' . $key . ']', $value);
    }
    $this->getSession()->getPage()->fillField('Street address', 'Andrássy út 22');
    $this->submitForm([], 'Submit');

    $this->profileStorage->resetCache([$profile_address2->id()]);
    /** @var \Drupal\profile\Entity\ProfileInterface $profile_address2 */
    $profile_address2 = $this->profileStorage->load($profile_address2->id());
    /** @var \Drupal\profile\Entity\ProfileInterface $new_profile_address */
    $new_profile_address = $this->profileStorage->load(3);

    /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $address */
    $address = $profile_address2->get('address')->first();
    $new_address = $new_profile_address->get('address')->first();

    $this->assertSession()->responseContains(new FormattableMarkup('Profile1 selected: :label', [':label' => 'Andrássy út 22']));
    $this->assertSession()->responseContains(new FormattableMarkup('Profile2 selected: :label', [':label' => $this->address2['address_line1']]));

    // Assert that field values have not changed.
    $this->assertEquals($this->address2['country_code'], $address->getCountryCode());
    $this->assertEquals($this->address2['given_name'], $address->getGivenName());
    $this->assertEquals($this->address2['family_name'], $address->getFamilyName());
    $this->assertEquals($this->address2['address_line1'], $address->getAddressLine1());
    $this->assertEquals($this->address2['locality'], $address->getLocality());
    $this->assertEquals($this->address2['postal_code'], $address->getPostalCode());
    // Assert that field values of new profile entity
    $this->assertEquals($this->address2['country_code'], $new_address->getCountryCode());
    $this->assertEquals($this->address2['given_name'], $new_address->getGivenName());
    $this->assertEquals($this->address2['family_name'], $new_address->getFamilyName());
    $this->assertEquals('Andrássy út 22', $new_address->getAddressLine1());
    $this->assertEquals($this->address2['locality'], $new_address->getLocality());
    $this->assertEquals($this->address2['postal_code'], $new_address->getPostalCode());
  }

  /**
   * Test profile selection changed
   */
  public function testProfileSelectAuthenticatedSelectionChanged() {
    $account = $this->createUser();
    /** @var \Drupal\profile\Entity\ProfileInterface $profile_address1 */
    $profile_address1 = $this->createEntity('profile', [
      'type' => 'customer',
      'uid' => $account->id(),
      'address' => $this->address1,
      'is_default' => TRUE,
    ]);
    /** @var \Drupal\profile\Entity\ProfileInterface $profile_address2 */
    $profile_address2 = $this->createEntity('profile', [
      'type' => 'customer',
      'uid' => $account->id(),
      'address' => $this->address2,
    ]);
    /** @var \Drupal\profile\Entity\ProfileInterface $profile_address3 */
    $profile_address3 = $this->createEntity('profile', [
      'type' => 'customer',
      'uid' => $account->id(),
      'address' => $this->address3,
    ]);

    $this->drupalLogin($account);

    // Edit a profile.
    $this->drupalGet(Url::fromRoute('commerce_order_test.multiple_profile_select_form'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldExists('profile1[profile_selection]');
    // The last created profile should be selected by default.
    $profile1_selector = '//div[@data-drupal-selector="edit-profile1"]';
    $profile2_selector = '//div[@data-drupal-selector="edit-profile2"]';
    $this->assertSession()->elementTextContains('xpath', $profile1_selector, $this->address1['locality']);
    $this->assertSession()->elementTextContains('xpath', $profile2_selector, $this->address1['locality']);

    $this->getSession()->getPage()->pressButton('profile1_edit_profile');
    $this->waitForAjaxToFinish();
    $this->getSession()->getPage()->fillField('profile1[profile_selection]', $profile_address2->id());
    $this->waitForAjaxToFinish();
    foreach ($this->address2 as $key => $value) {
      $this->assertSession()->fieldValueEquals('profile1[address][0][address][' . $key . ']', $value);
    }

    $this->getSession()->getPage()->pressButton('profile2_edit_profile');
    $this->waitForAjaxToFinish();
    $this->getSession()->getPage()->fillField('profile2[profile_selection]', $profile_address3->id());
    $this->waitForAjaxToFinish();
    foreach ($this->address3 as $key => $value) {
      $this->assertSession()->fieldValueEquals('profile2[address][0][address][' . $key . ']', $value);
    }
  }
}
