using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Blazorise;
using CCM_WebClient.Translation;
using Domain;
using Domain.Protocol;
using Microsoft.AspNetCore.Components;
using Microsoft.AspNetCore.Components.Forms;
using Microsoft.JSInterop;
using Radzen.Blazor;
using Serilog;
using Serilog.Core;
using Services;

namespace CCM_WebClient.Models
{
    public class UsersModel: BaseModel
    {
        [Inject] UserService UserService { get; set; }
        [Inject] private AccountService AccountService { get; set; }
        
        protected readonly ILogger logger = Log.Logger;

        public UserAccountModel UserAccount { get; set; }

        protected string AccountPassword { get; set; }

        protected Validations validations;

        protected RadzenGrid<User> grid;
        
        
        public UsersModel()
        {
            UserAccount = new UserAccountModel();
        }

        public List<User> UserList { get; set; }

        protected override void OnInitialized()
        {
            this.UserList = UserService.GetAllUsers();
            
        }

        public void SaveUser()
        {

            if (!validations.ValidateAll())
            {
                ShowError(T._("Form invalid"), T._("There are some mistakes on the form") + " Code:204");
                return;
            }
            
            if(UserAccount.User != null && UserAccount.Account != null ){
                var acctResult = AccountService.Save(UserAccount.Account);
                if (acctResult.Status != ObjectOperationStatus.Created &&  acctResult.Status != ObjectOperationStatus.Updated )
                {
                    ShowError(T._("Invalid Operation"), T._("Error creating/updating account") + " Code:203 Message:" + acctResult.Message);
                    return;
                }

                UserAccount.User.AccountId = acctResult.IdRef;
                
                var result = UserService.SaveUser(UserAccount.User);
                if (result.Status != ObjectOperationStatus.Created &&  result.Status != ObjectOperationStatus.Updated )
                {
                    // Deleting the created account
                    AccountService.Delete(UserAccount.Account.Id);
                    
                    ShowError(T._("Invalid Operation"), T._("Error creating/updating user") + " Code:202");
                }
                else
                {
                    UserAccount.User.Id = result.IdRef;
                    
                    UserList.Add(UserAccount.User);
                    
                    ShowInfo(T._("Success"), T._("User updated successfully"));
                    StateHasChanged();
                }
                
            }
            else
            {
                ShowWarning(T._("Invalid Operation"), " Code:201");
            }
        }

        public void NewUser()
        {

            UserAccount.User = new User()
            {
                Id = -1
            };
            UserAccount.Account = new Account()
            {
                Id = -1
            };
        
            //ShowInfo( T._("Information"), T._("Enter new user data"));
            StateHasChanged();
        }
        
        public void DeleteUser()
        {
            if (UserAccount.User == null || UserAccount.Account == null)
            {
                ShowError(T._("Error"), T._("User or account cannot be NULL"));
                return;
            }

            if (UserAccount.User.Id > 0 && UserAccount.Account.Id > 0)
            {
                var resultAcct = AccountService.Delete(UserAccount.Account.Id);

                if (resultAcct.Status == ObjectOperationStatus.Deleted)
                {
                    var resultUsr = UserService.Delete(UserAccount.User.Id);
                    if (resultUsr.Status == ObjectOperationStatus.Deleted)
                    {
                        ShowInfo( T._("Information"), T._("User deleted"));
                        
                        UserList.Remove(UserAccount.User);
                        grid.EditRow(UserList.FirstOrDefault());
                        
                        UserAccount.User = UserList.FirstOrDefault();
                        UserAccount.Account = AccountService.GetUserAccount(UserAccount.User);

                        StateHasChanged();
                        return;
                    }
                    ShowError(T._("Error"), T._("Error deleting user"));
                    return;
                }
                
                ShowError(T._("Error"), T._("Error deleting account"));
                return;
                
            }
            

        }

        public void SelectUser(User user)
        {
            
            UserAccount.User = user;
            if (UserAccount.User.Id != -1)
            {
                var account = AccountService.GetUserAccount(user);
                UserAccount.Account = account;
            }

            StateHasChanged();
        }
        

        public bool CanNotSave
        {
            get { return UserAccount.User == null;  }
        }
    }
}