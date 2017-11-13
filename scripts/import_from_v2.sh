#!/bin/bash
[ -d database/v2 ] || mkdir database/v2
echo "Downloading: countries"
wget https://mathrix.fr/test?table=countries -O database/v2/countries.json
echo "Downloaded:  countries"
echo "Downloading: classes"
wget https://mathrix.fr/test?table=classes -O database/v2/classes.json
echo "Downloaded:  classes"
echo "Downloading: disciplines"
wget https://mathrix.fr/test?table=disciplines -O database/v2/disciplines.json
echo "Downloaded:  disciplines"
echo "Downloading: chapters"
wget https://mathrix.fr/test?table=chapters -O database/v2/chapters.json
echo "Downloaded:  chapters"
echo "Downloading: notions"
wget https://mathrix.fr/test?table=notions -O database/v2/notions.json
echo "Downloaded:  notions"
echo "Downloading: activities"
wget https://mathrix.fr/test?table=activities -O database/v2/activities.json
echo "Downloaded:  activities"
echo "Downloading: modules"
wget https://mathrix.fr/test?table=modules -O database/v2/modules.json
echo "Downloaded:  modules"
echo "Downloading: class_disicpline"
wget https://mathrix.fr/test?table=class_discipline -O database/v2/class_discipline.json
echo "Downloaded:  class_disicpline"
echo "Downloading: activity_notion"
wget https://mathrix.fr/test?table=notions_activities -O database/v2/activity_notion.json
echo "Downloaded:  activity_notion"
echo "Downloading: activities_modules"
wget https://mathrix.fr/test?table=activities_modules -O database/v2/activity_module.json
echo "Downloaded:  activities_modules"

echo "Downloading: categories"
wget https://mathrix.fr/test?table=categories -O database/v2/categories.json
echo "Downloaded:  categories"
echo "Downloading: posts"
wget https://mathrix.fr/test?table=posts -O database/v2/posts.json
echo "Downloaded:  posts"

echo "Downloading: users"
wget https://mathrix.fr/test?table=users -O database/v2/users.json
echo "Downloaded:  users"
echo "Downloading: subscriptions"
wget https://mathrix.fr/test?table=subscriptions -O database/v2/subscriptions.json
echo "Downloaded:  subscriptions"

php artisan migrate:refresh
php artisan db:seed --class=ImportSeeder