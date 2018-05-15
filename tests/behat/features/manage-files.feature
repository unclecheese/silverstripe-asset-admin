@javascript @assets @retry
Feature: Manage files
  As a cms author
  I want to upload and manage files within the CMS
  So that I can insert them into my content efficiently

  Background:
    Given a "image" "assets/folder1/file1.jpg" was created "2012-01-01 12:00:00"
      And a "image" "assets/folder1/folder1-1/file2.jpg" was created "2010-01-01 12:00:00"
      And a "folder" "assets/folder2"
      And a page "Gallery" containing an image "assets/folder3/file1.jpg"
      And I am logged in with "ADMIN" permissions
      And I go to "/admin/assets"

  Scenario: I can delete multiple files
    Given a "image" "assets/folder1/file2.jpg" was created "2012-01-02 12:00:00"
    When I click on the file named "folder1" in the gallery
      And I check the file named "file1" in the gallery
    Then I should see an ".bulk-actions__action[value='edit']" element
      And the ".bulk-actions-counter" element should contain "1"
    When I check the file named "file2" in the gallery
    Then the ".bulk-actions__action[value='delete']" element should contain "Delete"
      And the ".bulk-actions-counter" element should contain "2"
      And I should not see an ".bulk-actions__action[value='edit']" element
    When I attach the file "testfile.jpg" to dropzone "gallery-container"
      And I check the file named "testfile" in the gallery
    Then the ".bulk-actions-counter" element should contain "3"
      And I press the "Delete" button, confirming the dialog
      And I wait for 5 seconds
    Then I should not see the file named "file1" in the gallery
      And I should not see the file named "file2" in the gallery
      And I should not see the file named "testfile" in the gallery
      And I should see "3 folders/files were successfully archived" in the message box
