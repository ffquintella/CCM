using System.Collections.Generic;
using Blazorise.DataGrid;
using CCM_WebClient.Translation;
using Domain;
using Domain.Protocol;
using Microsoft.AspNetCore.Components;
using Services;
using System;
using System.Threading.Tasks;
using Blazorise;

namespace CCM_WebClient.Models
{
    public class GroupsPageModel: BaseModel
    {
        [Inject] UserGroupService UserGroupService { get; set; }
        [Inject] UserService UserService { get; set; }
        
        [Inject] RoleService RoleService { get; set; }
        protected List<UserGroup> Groups { get; set; }

        protected UserGroup SelectedGroup { get; set; }
        
        protected User SelectedUser { get; set; }
        protected long SelectedUserId { get; set; } = -1;
        
        protected List<User> Users { get; set; }

        protected List<Role> Roles { get; set; }
        
        protected Modal deleteModal;

        protected Object selectedUserToAdd;

        public Action<CancellableRowChange<UserGroup, Dictionary<string, object>>> InsertingAction { get; set; }


        public GroupsPageModel()
        {
            InsertingAction = OnInserting;
        }
        
        protected override void OnInitialized()
        {
            Groups = new List<UserGroup>();

            Users = UserService.GetAllUsers();
            Roles = RoleService.GetAll();
        }

        protected int totalGroups;

        protected async Task OnReadData(DataGridReadDataEventArgs<UserGroup> e)
        {
            var groups = await UserGroupService.GetAllAsync();
            if (groups == null)
            {
                Groups = groups;
                totalGroups = 0;
            }
            else
            {
                Groups = groups;
                totalGroups = groups.Count;
            }
        }

        protected void OnInserting(CancellableRowChange<UserGroup, Dictionary<string, object>> crc)
        {
            var groupExists = UserGroupService.NameExists((string)crc.Values["Name"]);
            if (groupExists)
            {
                crc.Cancel = true;
                ShowError(T._("Group creation"), T._("This group name already exists"));
            }
        }
        
        protected void OnRowInserted(SavedRowItem<UserGroup, Dictionary<string, object>> e)
        {
            // The user is new 

            var userGroup = e.Item;
            userGroup.Id = -1;
            var result = UserGroupService.Create(userGroup);

            switch (result.Status)
            {
                case ObjectOperationStatus.Created:
                    ShowInfo(T._("Group creation"), T._("Group created successfully"));
                    break;
                case ObjectOperationStatus.Forbidden:
                    ShowWarning( T._("Group creation"), T._("You don't have authorization to proceed"));
                    break;
                default:
                    ShowError(T._("Group creation"), T._("Unkown error"));
                    break;
            }

            e.Item.Id = result.IdRef;
        }
        
        protected void OnRemoving(CancellableRowChange<UserGroup> crc)
        {
            ShowDeleteModal();
            crc.Cancel = true;
        }

        protected void HandleUserSearch(Object userid)
        {
            SelectedUserId = (long) userid;
        }

        protected bool GroupHasRole(long roleId)
        {
            if (SelectedGroup == null) return false;

            if (SelectedGroup.RolesIds.Contains(roleId)) return true;

            return false;
        }
        
        protected void HandleCheckChange(long roleid)
        {
            if (SelectedGroup.RolesIds.Contains(roleid)) SelectedGroup.RolesIds.Remove(roleid);
            else SelectedGroup.RolesIds.Add(roleid);
            //SelectedUserId = (long) userid;
        }

        protected void UpdateSelectedGroup()
        {
            if (SelectedGroup == null) return;

            var response = UserGroupService.Update(SelectedGroup);

            if (response.Status == ObjectOperationStatus.Updated)
            {
                ShowInfo(T._("Group update"), T._("Selected user updated"));
                return;
            }
            
            ShowError(T._("Group update"), T._("Error updating selected user. Message:" + " " + response.Message));
        }
        
        protected void AddUserToGroup()
        {
            if (SelectedUserId < 0 )
            {
                ShowWarning( T._("Adding user to group"), T._("The selected user is invalid"));
                return;
            }

            if (SelectedGroup == null)
            {
                ShowWarning( T._("Adding user to group"), T._("The selected group is invalid"));
                return;
            }

            if (SelectedGroup.UsersIds.Contains(SelectedUserId))
            {
                ShowWarning( T._("Adding user to group"), T._("The selected user is already in group"));
                return; 
            }
            
            SelectedGroup.UsersIds.Add(SelectedUserId);

            var result = UserGroupService.Update(SelectedGroup);

            if (result.Status == ObjectOperationStatus.Updated)
            {
                ShowInfo(T._("Adding user to group"), T._("User added successfully"));
                return;
            }
            
            // if we get here we have an error and should rollback the change
            SelectedGroup.UsersIds.Remove(SelectedUserId);
            ShowError(T._("Adding user to group"), T._("Error adding user to the group. Message:" + " " + result.Message));


        }
        
        /*protected void OnRowRemoved(UserGroup group)
        {
     
            var result = UserGroupService.Delete(group.Id);

            switch (result.Status)
            {
                case ObjectOperationStatus.Deleted:
                    ShowInfo(T._("Group deleted"), T._("Group deleted successfully"));
                    break;
                case ObjectOperationStatus.Forbidden:
                    ShowWarning( T._("Group not deleted"), T._("You don't have authorization to proceed"));
                    break;
                default:
                    ShowError(T._("Group not deleted"), T._("Unkown error"));
                    break;
            }
            
        }*/

        protected void OnRowUpdated( SavedRowItem<UserGroup, Dictionary<string, object>> e )
        {
            var group = e.Item;

            var grpNewName = (string)e.Values["Name"];
            
            var groupExists = UserGroupService.NameExists(grpNewName);
            if (groupExists)
            {
                ShowError(T._("Group update"), T._("This group name already exists"));
            }
            else
            {
                var oldName = group.Name;
                group.Name = (string)e.Values["Name"];
                var response = UserGroupService.Update(group);
                if (response.Status == ObjectOperationStatus.Updated)
                {
                    ShowInfo(T._("Group update"), T._("Group updated successfully"));
                }
                else
                {
                    ShowError(T._("Group update"), response.Message);
                    group.Name = oldName;
                }
            }
        }

        protected List<User> ListUsersOfGroup(UserGroup group = null)
        {
            if (group == null)
            {
                group = SelectedGroup;
                if (group == null)
                {
                    return new List<User>();
                }
            }
            
            var users = UserService.GetUsersInList(group.UsersIds);
            
            return users;
        }
        
        protected void ShowDeleteModal()
        {
            deleteModal.Show();
        }
    
        protected void HideDeleteModal()
        {
            deleteModal.Hide();
        }

        protected void RemoveUserFormGroup(long id)
        {
            if (SelectedGroup == null ) return;

            SelectedGroup.UsersIds.Remove(id);
            var result = UserGroupService.Update(SelectedGroup);

            if (result.Status == ObjectOperationStatus.Updated)
            {
                ShowInfo(T._("Group update"), T._("User removed from group"));
            }
            else
            {
                ShowError(T._("Group update"), result.Message);
                SelectedGroup.UsersIds.Add(id);
            }
        }
        
        protected void ConfirmDeletion()
        {
            
            var result = UserGroupService.Delete(SelectedGroup.Id);

            switch (result.Status)
            {
                case ObjectOperationStatus.Deleted:
                    ShowInfo(T._("Group deleted"), T._("Group deleted successfully"));
                    var grp = Groups.Find(g => g.Id == SelectedGroup.Id);
                    Groups.Remove(grp);
                    break;
                case ObjectOperationStatus.Forbidden:
                    ShowWarning( T._("Group not deleted"), T._("You don't have authorization to proceed"));
                    break;
                default:
                    ShowError(T._("Group not deleted"), T._("Unkown error"));
                    break;
            }

            SelectedGroup = null;
            
            deleteModal.Hide();
        }
        
    }
}