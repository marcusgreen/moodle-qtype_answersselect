@core @qtype @qtype_answersselect @qtype_answersselect_basic @_switch_window
Feature: Test all the basic functionality of this Answers Select question type
    In order to evaluate students responses, As a teacher I need to
    create and preview gapfill questions.

  Background:
    Given the following "users" exist:
        | username | firstname | lastname | email               |
        | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
    And the following "courses" exist:
        | fullname | shortname | category |
        | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
        | user     | course | role           |
        | teacher1 | C1     | editingteacher |

  @javascript
  Scenario: Create, edit then preview a gapfill question.
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Questions" in current page administration

    # Create a new question.
    And I add a "Random select answers" question filling the form with:
        | Question name           | ASelect-001              |
        | Question text           | Select five              |
        | General feedback        | This is general feedback |
        | Choice 1                | Five                     |
        | id_correctanswer_0      | true                     |
        | Choice 2                | Two                      |
        | Choice 3                | Three                    |
        | Hint 1                  | Hint 1                   |
        | id_hintshownumcorrect_0 | 1                        |
        | Hint 2                  | Hint 2                   |
        | id_hintshownumcorrect_1 | 1                        |

    Then I should see "ASelect-001"

    When I choose "Preview" action for "ASelect-001" in the question bank
    And I switch to "questionpreview" window
    #################################################
    #Interactive with multiple tries
    #################################################
    And I set the following fields to these values:
        | How questions behave | Interactive with multiple tries |
        | Marked out of        | 2                               |
        | Marks                | Show mark and max               |
        | Specific feedback    | Shown                           |
        | Right answer         | Shown                           |
    And I press "Start again with these options"
    And I click on "Five" "qtype_answersselect > Answer"
    And I press "Check"
    And I should see "Your answer is correct."
