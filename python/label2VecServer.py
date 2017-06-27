
# coding: utf-8

# In[37]:

import gensim
import jieba
import numpy as np
import json
import MySQLdb as mysql
from numpy import*


# In[38]:

con = mysql.connect("localhost","root","AILab@C408","images_classifier",charset='utf8')
db = con.cursor()
imagesVec = dict()


# In[3]:
print('init')
model = gensim.models.KeyedVectors.load_word2vec_format("wiki.zh.text.vector",binary=False)
print('model initialization successfully')

# In[117]:

def getUserVecByDB(user_id):
    global db
    userLabelVecs = []
    userVec = []
    db.execute('SELECT label_name FROM image_label INNER JOIN label ON label.label_id = image_label.label_id WHERE users_added LIKE "%'+user_id+'%"')
    userLabels = db.fetchall()
    for labelTup in userLabels:
        text = labelTup[0].encode('utf8')
        vec = text2vec(text)
        if len(vec) != 0:
            userLabelVecs.append(vec.tolist())
    userLabelVecs = np.array(userLabelVecs)
    userVec = np.sum(userLabelVecs,axis=0).tolist()
    return userVec


def getImageVecByDB():
    global imagesVec,db
    #imagesVec = dict()
    if len(imagesVec)== 0:
        #初始化全部图像信息
        db.execute('SELECT image_id,label_name FROM image_label INNER JOIN label ON label.label_id = image_label.label_id WHERE image_id in(SELECT image_id FROM image WHERE is_del = 0) ')
        allImages = db.fetchall()
        #print allImages
        for image in allImages:
            image_id = image[0].encode('utf8')
            label_name = image[1].encode('utf8')
           
            if not imagesVec.has_key(image_id):
                imagesVec[image_id] = []
            imagesVec[image_id].append(label_name)
        for image_id in imagesVec:
            imageVec = []
            for label_name in imagesVec[image_id]:
                vec = text2vec(label_name)
                if len(vec) != 0:
                    imageVec.append(vec.tolist())
            imageVec = np.array(imageVec)
            imageVec = np.sum(imageVec,axis=0).tolist()
            imagesVec[image_id] = imageVec
    else:
        #否则的话只更新信息更新了的图片
        db.execute('SELECT image_id,label_name FROM image_label INNER JOIN label ON label.label_id = image_label.label_id WHERE image_id in(SELECT image_id FROM image WHERE updated = 1 and is_del = 0)')
        updatedImages = db.fetchall()
        db.execute('UPDATE image SET updated = 0 WHERE updated = 1')
        updatedimagesVec = dict()
        #print(updatedImages)
        for image in updatedImages:
            image_id = image[0].encode('utf8')
            label_name = image[1].encode('utf8')

            if not updatedimagesVec.has_key(image_id):
                updatedimagesVec[image_id] = []
            updatedimagesVec[image_id].append(label_name)
        for image_id in updatedimagesVec:
            imageVec = []
            for label_name in updatedimagesVec[image_id]:
                vec = text2vec(label_name)
                if len(vec) != 0:
                    imageVec.append(vec.tolist())
            imageVec = np.array(imageVec)
            imageVec = np.sum(imageVec,axis=0).tolist()
            imagesVec[image_id] = imageVec
    
    #删除已经标记结束的图片
    #print imagesVec['8c9df18b392ec80f5ec59d77901f6035']
    #db.execute('UPDATE image SET status = 0 WHERE  1')
    #db.execute('SELECT image_id FROM image WHERE status = 2')
    #needToDeleteImages = db.fetchall()
    #for image in needToDeleteImages:
    #    image_id = image[0].encode('utf8')
    #    if imagesVec.has_key(image_id):
    #        imagesVec.pop(image_id)
    #print imagesVec['8c9df18b392ec80f5ec59d77901f6035']
        

def text2vec(text):
    global model
    vecs = []
    seg_list = jieba.cut(text,cut_all=False)
    for word in seg_list:
        if word in model:
            vecs.append(model[word])
    vecs = np.array(vecs)
    if len(vecs) == 0:
        return []
    else:
        objVec = np.sum(vecs,axis=0)
        return objVec/len(vecs)

def cos(vector1,vector2):
    #vector1 = vector1[0][0]
    #vector2 = vector2[0][0]
    if linalg.norm(vector1)*linalg.norm(vector2) == 0:
    	return 0
    else:
    	return dot(vector1,vector2)/(linalg.norm(vector1)*linalg.norm(vector2))
    
def getAllUserMarkedImages(user_id):
    db.execute('SELECT image_id FROM image_label WHERE users_added LIKE "%'+user_id+'%"');
    ResultAllUserMarkedImages = db.fetchall()
    AllUserMarkedImages = []
    for image in ResultAllUserMarkedImages:
        AllUserMarkedImages.append(image[0].encode('utf8'))
    return AllUserMarkedImages
    
    
def calculateSimlar(user_id):
    global imagesVec
    simlarValue = dict()
    userVec = getUserVecByDB(user_id)
    AllUserMarkedImages = getAllUserMarkedImages(user_id)
    #print imagesVec
    getImageVecByDB()
    for image_id in imagesVec:
        if image_id not in AllUserMarkedImages:
            simlarValue[image_id] = cos(userVec,imagesVec[image_id])
    temp = sorted(simlarValue.iteritems(), key=lambda a:a[1], reverse = True)
    simlarValue = []
    #print temp
    for obj in temp:
        if obj[1]>0.5:
            simlarValue.append(obj[0])
    return simlarValue[:150]

def getImagesByLabels(labels):
    labelsVec = []
    for label in labels:
        vecs = []
        seg_list = jieba.cut(label,cut_all=False)
        for word in seg_list:
            if word in model:
                #print word
                vecs.append(model[word])
        vecs = np.array(vecs)
        if len(vecs) == 0:
            labelsVec.append([])
        else:
            objVec = np.sum(vecs,axis=0)
            objVec = objVec/len(vecs)
            labelsVec.append(objVec)
    return labelsVec

def searchVaguelyImages(labels):
    global imagesVec
    imageIds = []
    labelsVec = getImagesByLabels(labels)
    getImageVecByDB()
    #print imagesVec
    for labelVec in labelsVec:
        #print labelVec
        if len(labelVec) == 0:
            imageIds.append([])
        else:
            simlarValue = dict()
            for image_id in imagesVec:
                simlarValue[image_id] = cos(labelVec,imagesVec[image_id])
            temp = sorted(simlarValue.iteritems(), key=lambda a:a[1], reverse = True)
            simlarValue = []
            for obj in temp:
                if obj[1]>0.1:
                    simlarValue.append(obj[0])
            imageIds.append(simlarValue)
    return imageIds


# In[63]:


import socket,time
host='0.0.0.0'
port=12308
s=socket.socket(socket.AF_INET,socket.SOCK_STREAM) #定义socket类型
s.bind((host,port)) #绑定需要监听的Ip和端口号，tuple格式
s.listen(1) #开始监听TCP传入连接。指定最多允许多少个客户连接到服务器。它的值至少为1。收到连接请求后，这些请求需要排队，如果队列满，就拒绝请求。大部分应用程序设为5就可以了
print('socket started')
#建立长连接，数据传输完成后连接不自动关闭，等待超时自动关闭
while True:
    connection,address=s.accept()
    print('Connected by ',address)
    try:
        connection.settimeout(5) #定义超时时间
        buf = connection.recv(1049) #服务端接收到的从客户端发送过来的数据。在Python3中接收到的数据默认格式为bytes，需要进行解码转换为string
        parameters = buf.split(':')
        if parameters[0] == 'push':
            #push图片
            user_id = parameters[1]
            simlarValue = calculateSimlar(user_id)
            json_string = json.dumps(simlarValue)
        else:
            #模糊查找
            labels = parameters[1].split(',')
            imageIds = searchVaguelyImages(labels)
            json_string = json.dumps(imageIds)
        connection.send(json_string) #sendall() 发送完整的TCP连接数据
        connection.close()
        
    except socket.timeout:
        connection.close()
    #else:
    #connection.close() #传输结束，服务器调用socket的close方法关闭连接，之后client还可以重新发起连接传输数据


# In[ ]:



