using System.ComponentModel.DataAnnotations;
using Domain;

namespace CCM_WebClient.Models
{
    public class UserAccountModel
    {

        public UserAccountModel()
        {
            //User = new User();
            //Account = new Account();
        }
        
        public User User { get; set; }

        private Account _account;
        public Account Account {
            get { return _account; }
            set { _account = value; } }
    }
}