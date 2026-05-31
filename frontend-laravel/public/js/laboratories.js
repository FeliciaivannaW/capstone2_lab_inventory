document.addEventListener('alpine:init', () => {
    Alpine.data('laboratoriesData', () => ({
        activeModal: null,
        fixedGroupId: null,
        formGroupId: '',
        formUserId: '',
        formRoomId: '',
        rows: [0],
        editData: {},
        detailData: {},
        groupDetail: { loading: false, users: [], rooms: [] },
        init() {
            this.$watch('formGroupId', () => this.loadGroupMembersIfChanged());
        },
        async loadGroupMembersIfChanged() {
            if (this.formGroupId) {
                if (this.detailData.id == this.formGroupId && !this.groupDetail.loading) return;
                try {
                    const res = await fetch('/lab-groups/' + this.formGroupId + '/details');
                    const json = await res.json();
                    if (json.status === 'success') {
                        this.detailData = json.data;
                        this.groupDetail.users = json.data.users || [];
                        this.groupDetail.rooms = json.data.rooms || [];
                        window.dispatchEvent(new CustomEvent('update-group-counts', {
                            detail: {
                                groupId: this.detailData.id,
                                users: this.groupDetail.users.length,
                                rooms: this.groupDetail.rooms.length
                            }
                        }));
                    }
                } catch(e) {}
            } else {
                this.groupDetail = { loading: false, users: [], rooms: [] };
            }
        },
        isUserInGroup(userId) {
            return this.groupDetail.users.some(u => u.user_id == userId);
        },
        isRoomInGroup(roomId) {
            return this.groupDetail.rooms.some(r => r.room_id == roomId);
        },
        async openGroupDetail(groupId) {
            this.groupDetail = { loading: true, users: [], rooms: [] };
            this.detailData = {};
            this.activeModal = 'detail_grup';
            try {
                const res = await fetch('/lab-groups/' + groupId + '/details');
                const json = await res.json();
                if (json.status === 'success') {
                    this.detailData = json.data;
                    this.groupDetail.users = json.data.users || [];
                    this.groupDetail.rooms = json.data.rooms || [];
                    window.dispatchEvent(new CustomEvent('update-group-counts', {
                        detail: {
                            groupId: this.detailData.id,
                            users: this.groupDetail.users.length,
                            rooms: this.groupDetail.rooms.length
                        }
                    }));
                }
            } catch (e) { console.error(e); }
            this.groupDetail.loading = false;
        },
        toast: { show: false, message: '', type: 'success' },
        showToast(message, type = 'success') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => { this.toast.show = false; }, 3000);
        },
        async deleteGroup(id, event) {
            if (!confirm('Apakah Anda yakin ingin menghapus grup lab ini secara permanen?')) return;
            try {
                const res = await fetch('/lab-groups/' + id, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                });
                const json = await res.json();
                if (res.ok) {
                    this.showToast(json.message || 'Grup lab berhasil dihapus');
                    event.target.closest('tr').remove();
                } else {
                    alert(json.message || 'Gagal menghapus grup lab');
                    this.showToast(json.message || 'Gagal menghapus grup lab', 'error');
                }
            } catch (e) { 
                alert('Terjadi kesalahan jaringan');
                this.showToast('Terjadi kesalahan jaringan', 'error'); 
            }
        },
        async deleteLab(id, event) {
            if (!confirm('Apakah Anda yakin ingin menghapus laboratorium ini secara permanen?')) return;
            try {
                const res = await fetch('/laboratories/' + id, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                });
                const json = await res.json();
                if (res.ok) {
                    this.showToast(json.message || 'Laboratorium berhasil dihapus');
                    event.target.closest('tr').remove();
                } else {
                    this.showToast(json.message || 'Gagal menghapus laboratorium', 'error');
                }
            } catch (e) { this.showToast('Terjadi kesalahan jaringan', 'error'); }
        },
        async removeGroupUser(groupId, userId) {
            if (!confirm('Hapus user ini dari grup?')) return;
            try {
                const res = await fetch('/lab-groups/' + groupId + '/users/' + userId, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                });
                if (res.ok) { 
                    this.showToast('User berhasil dihapus dari grup');
                    this.openGroupDetail(groupId); 
                }
            } catch (e) { console.error(e); }
        },
        async removeGroupRoom(groupId, roomId) {
            if (!confirm('Hapus ruangan ini dari grup?')) return;
            try {
                const res = await fetch('/lab-groups/' + groupId + '/rooms/' + roomId, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                });
                if (res.ok) { 
                    this.showToast('Ruangan berhasil dihapus dari grup');
                    this.openGroupDetail(groupId); 
                }
            } catch (e) { console.error(e); }
        }
    }));
});
