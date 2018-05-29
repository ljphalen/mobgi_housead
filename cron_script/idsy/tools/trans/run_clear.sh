#/bin/bash
SQL_PATH="$(pwd)/sql"
SQL_COMMAND="/usr/bin/mysql -h172.16.201.56 -uad_system -pwY7DTW6aBXV9ljG_g4sE -P5029 --local-infile=1 -e"
TABLE="bh_ad_stats.video_ads_stat_2016"

for file in $(find $SQL_PATH -cmin +2 -name "*.sql")
do
	#echo $file
	sed -i '/ufffd/d' $file
	result=$(/usr/bin/mysql -h172.16.201.56 -uad_system -pwY7DTW6aBXV9ljG_g4sE -P5029 --local-infile=1 -e "LOAD DATA LOCAL INFILE '$file' INTO TABLE $TABLE character set utf8 FIELDS TERMINATED BY '\t' ENCLOSED BY '' escaped by '' LINES TERMINATED BY '\n' STARTING BY '' "  && rm -rf $file 2>&1)
   	if [[ $? -eq 0 ]];then
		rm -rf $file 2>&1
		echo 'ok'
	else
		echo $result
		row=$(echo $result|awk -F: '{print $4}'|awk -F, '{print $1}'|sed 's/\ //')
		echo  "sed:==$row=="
		# cat $file |tail -n +$row| head -n 1
		sed -n "$(row)p" $file
		sed -i "$(row)"d $file
	fi
done
