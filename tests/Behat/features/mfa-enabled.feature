Feature: MFA is enabled for the site
  As a website owner
  I want to enable multi-factor authentication for my site
  So that my site will be more secure

  Background:
    Given I am logged in with "ADMIN" permissions
    And I go to "/admin"
    Then I should see the CMS

  Scenario: I can set MFA to be required for all users
    Given I go to "/admin/settings"
    And I click the "Access" CMS tab
    Then I should see "Multi-factor authentication (MFA)"
    When I select "MFA is required" from the MFA settings
    And I press "Save"
    Then I should see a "Saved" success toast

  # This scenario must be before any other "select a group" scenario, since the saved settings
  # aren't reset between scenarios.
  Scenario: I must add at least one group if requiring MFA for groups
    Given I go to "/admin/settings"
    And I click the "Access" CMS tab
    Then I should see "Multi-factor authentication (MFA)"
    When I select "MFA is required" from the MFA settings
    And I select "Only these groups (choose from list)" from "Who do these MFA settings apply to?" input group
    And I press "Save"
    Then I should not see a "Saved" success toast
    Then I should see "At least one group must be selected, or the MFA settings should apply to everyone."

  Scenario: I can set MFA to be required for a given group
    Given I go to "/admin/settings"
    And I click the "Access" CMS tab
    Then I should see "Multi-factor authentication (MFA)"
    When I select "MFA is required" from the MFA settings
    Then I should not see "MFA Groups"
    When I select "Only these groups (choose from list)" from "Who do these MFA settings apply to?" input group
    Then I should see "MFA Groups"
    When I select "ADMIN group" in the "#Form_EditForm_MFAGroupRestrictions_Holder" tree dropdown
    And I press "Save"
    Then I should see a "Saved" success toast
    Then I should not see "At least one group must be selected, or the MFA settings should apply to everyone."

  Scenario: I can set MFA to be optional for a given group
    Given I go to "/admin/settings"
    And I click the "Access" CMS tab
    Then I should see "Multi-factor authentication (MFA)"
    When I select "MFA is optional" from the MFA settings
    And I select "Only these groups (choose from list)" from "Who do these MFA settings apply to?" input group
    And I select "ADMIN group" in the "#Form_EditForm_MFAGroupRestrictions_Holder" tree dropdown
    And I press "Save"
    Then I should see a "Saved" success toast
    Then I should not see "At least one group must be selected, or the MFA settings should apply to everyone."
