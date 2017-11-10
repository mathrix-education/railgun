#!/bin/bash
echo "Downloading: countries"
wget https://mathrix.fr/test?table=countries -O ../database/raws/countries.json
echo "Downloaded:  countries"
echo "Downloading: classes"
wget https://mathrix.fr/test?table=classes -O ../database/raws/classes.json
echo "Downloaded:  classes"
echo "Downloading: disciplines"
wget https://mathrix.fr/test?table=disciplines -O ../database/raws/disciplines.json
echo "Downloaded:  disciplines"
echo "Downloading: chapters"
wget https://mathrix.fr/test?table=chapters -O ../database/raws/chapters.json
echo "Downloaded:  chapters"
echo "Downloading: notions"
wget https://mathrix.fr/test?table=notions -O ../database/raws/notions.json
echo "Downloaded:  notions"
echo "Downloading: activities"
wget https://mathrix.fr/test?table=activities -O ../database/raws/activities.json
echo "Downloaded:  activities"
echo "Downloading: modules"
wget https://mathrix.fr/test?table=modules -O ../database/raws/modules.json
echo "Downloaded:  modules"
echo "Downloading: activity_notion"
wget https://mathrix.fr/test?table=notions_activities -O ../database/raws/activity_notion.json
echo "Downloaded:  activity_notion"
echo "Downloading: activities_modules"
wget https://mathrix.fr/test?table=activities_modules -O ../database/raws/activity_module.json
echo "Downloaded:  activities_modules"
