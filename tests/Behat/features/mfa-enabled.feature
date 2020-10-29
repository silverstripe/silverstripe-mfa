Feature: MFA is enabled for the site
  As a website owner
  I want to enable multi-factor authentication for my site
  So that my site will be more secure

  Background:
    Given I am logged in with "ADMIN" permissions
    And I go to "/admin"
    Then I should see the CMS

  Scenario: I can set MFA to be required
    Given I go to "/admin/settings"
    And I click the "Access" CMS tab
    Then I should see "Multi-factor authentication (MFA)"
    When I select "MFA is required for everyone" from the MFA settings
    And I press "Save"
    Then I should see a "Saved" success toast
