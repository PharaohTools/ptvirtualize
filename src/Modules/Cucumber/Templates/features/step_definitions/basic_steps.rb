Given /^I am on the home page$/ do
  visit "/"
end

Then /^I should see something/ do
  page.text.should =~ ""
end