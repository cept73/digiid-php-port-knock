#!/bin/bash
# Scan current fw rules, check records in IPs lists.
# Used with incron

# Where is IPs list
ips_path=/var/www/html/ips

# Function
array_contains () {
    local array="$1[@]"
    local seeking=$2
    local in=1
    for element in "${!array}"; do
        #echo $element '~' $seeking
        if [[ $element == $seeking ]]; then
            in=0
            break
        fi
    done
    return $in
}

# Is DigiID init initialized?
rules_list=`ufw status numbered | grep 'DigiID init' | awk '{ print $2 }'`
if [[ -z $rules_list ]]; then
    # Initialize
    ufw default deny incoming > /dev/null
    ufw allow 80 comment "DigiID init" > /dev/null
    ufw allow 443 comment "DigiID init" > /dev/null
fi

# Find DigiID rules
rules_list=`ufw status numbered | tac | grep 'DigiID right' | awk '{ print $6 "]" $2 }'`

# Current list of such elements - potentially to delete
current_rules_list=()
ip_of_num=()
for one in $rules_list; do
    IFS=']' read -r -a array <<< "$one"
    rule_number=${array[1]}
    rule_ip=${array[0]}
    current_rules_list+=($rule_number)
    ip_of_num[$rule_number]=$rule_ip
done

# Show file
to_add=()
for filename in $(ls -1 $ips_path); do
    to_add+=($filename)
done

# Remove old rules
for num in ${current_rules_list[@]}; do
    # Skip those we will add
    ip=${ip_of_num[num]}
    #echo $ip
    if (! array_contains to_add $ip); then
        # Delete other rules
        echo y | ufw delete $num > /dev/null
        continue
    fi
done

# Add new rules
for ip in ${to_add[@]}; do
#echo $ip ">>"
    # If already in, skip it
    if (! array_contains ip_of_num $ip); then
        # Append right to IP current_rules_list
        echo y | ufw insert 1 allow from $ip comment "DigiID right" > /dev/null
        continue
    fi
done

echo done
