FROM alexcheng/magento2:2.2-developer

ADD . app/code/Kustomer/KustomerIntegration

CMD ["sh", "-c", "sleep 10 ; install-magento ; /sbin/my_init"]
