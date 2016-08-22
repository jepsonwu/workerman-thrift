namespace php Services.Sms
service Sms
{
  string sendMsg(1:string mobile,2:string msg,3:string ip);
  string sendVoiceMsg(1:string mobile,2:string msg,3:string ip);
  string sendCaptcha(1:string mobile,2:string ip);
  string sendVoiceCaptcha(1:string mobile,2:string ip);
  string verifyCaptcha(1:string mobile,2:string captcha);
}
