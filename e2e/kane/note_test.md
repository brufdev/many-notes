---
mode: testing
url: http://localhost:4880/register
max_steps: 50
---

# Write a note

## Register and create a vault
Type "Sam Rivera" as the Name, "sam@example.com" as the Email and "Notes-Test-2026" as both the Password and Confirm password, then click "Register".
Click the "Create vault" button, type "Journal" as the Name and click "Save".
Verify the vault page opens with the title "Journal".

## Create a note
Click the "New note" button, type "Standup" as the Name and click "Save".
Click into the empty editor area below the toolbar and type "Ship the release on Friday".
Wait 3 seconds for the note to save.

## Check the note is saved
Reload the page.
Verify the note "Standup" is open and shows "Ship the release on Friday".
